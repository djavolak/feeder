import aiohttp
import xmltodict
import src.crudUtils as crud
import db
import pandas as pd
import json
import io
import zipfile
from ftplib import FTP
import paramiko
import csv
from cache import session

async def fetchProductsForSupplier(supplier):
    feedUrl = supplier.get("feedSource")
    username = supplier.get("feedUsername")
    password = supplier.get("feedPassword")
    loginUrl = supplier.get("feedLoginUrl")
    type = int(supplier.get("sourceType")) # 1 - http, 2 - ftp, 3 - sftp
    if type == 1:
        if (not username or not password or not loginUrl):
            response = await fetchDataFromUrl(feedUrl, session)
            data = await processDataBasedOnContentType(response)
        elif loginUrl:
            response = await loginAndFetchData(feedUrl, username, password, loginUrl, session)
            data = await processDataBasedOnContentType(response)
        return data
    elif type == 2:
        return await fetchDataFromFtp(feedUrl, username, password)
    elif type == 3:
        return await fetchDataFromSftp(feedUrl, username, password)
         
async def getSupplierFieldMapping(supplierId):
    return await crud.fetchBy('supplierFieldsMapping', 'supplierId', supplierId, db.poolEcomhelper)

async def getProductFieldMapping(supplierId):
    fieldMapping = pd.DataFrame(await getSupplierFieldMapping(supplierId))
    metaFields = fieldMapping.loc[fieldMapping['productFieldName'] == 'meta']['sourceFieldName'].values
    meta = None
    if metaFields.any():
       meta = metaFields.tolist()

    if(len(fieldMapping) == 0):
        raise Exception(f"Field mapping not found for supplier {supplierId}")
    productFields = await getParsedProductFields()
    if(len(productFields) == 0): 
        raise Exception(f"Product fields not found {supplierId}")
    foundMapping = False
    mappedFields = {}
    mappedFields['meta'] = None
    for fieldName in productFields:
        matchingRow = fieldMapping.loc[fieldMapping['productFieldName'] == fieldName]
        if not matchingRow.empty:
            mappedFields[fieldName] = matchingRow['sourceFieldName'].values[0]
            foundMapping = True
        else:
            mappedFields[fieldName] = None

    if meta:
        mappedFields['meta'] = meta
    if not foundMapping:
        raise Exception(f"Nothing is mapped for supplier: {supplierId}")
    return mappedFields

async def getParsedProductFields(): 

    async with db.poolEcomhelper.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute(f"DESCRIBE parsedProduct")
            columnsInfo = await cursor.fetchall()
            columnNames = [column['Field'] for column in columnsInfo if column['Field'] not in ['createdAt', 'updatedAt', 'meta']]
            columnNames.append('images')
            return columnNames
        
async def processDataBasedOnContentType(response: aiohttp.ClientResponse):
    contentType = response.headers.get('Content-Type', None)
    match contentType:
        case 'application/xml' | 'text/xml' | 'text/xml; charset=UTF-8':
            return await processXml(response)
        case 'application/json':
            return await processJson(response)
        case 'application/zip':
             #currently only supports zip files with one xml file inside
             return await processZip(response)
        case _:
            raise Exception(f"Unsupported content type: {contentType}. Expected: XML, JSON.")
        
async def processXml(response: aiohttp.ClientResponse, data = None):
    if not data:
        data = await response.text()
    dict = xmltodict.parse(data)
    rootElement = next(iter(dict))
    itemRootElement = next(iter(dict[rootElement]))
    return dict[rootElement][itemRootElement]

async def processJson(response: aiohttp.ClientResponse, data = None):
    if not data:
        data = await response.json()
    dict = json.loads(data)
    return dict

async def processZip(response: aiohttp.ClientResponse):
    with zipfile.ZipFile(io.BytesIO(await response.read())) as zip:
        for filename in zip.namelist():
            with zip.open(filename) as file:
                file = file.read()
                return await processXml(response, file)

#todo: change this so its not hardcoded for foxWay
async def processCsv(response: aiohttp.ClientResponse, data = None):
    if not data:
        data = await response.text()
    csvReader = csv.reader(io.StringIO(data.decode('utf-8', 'replace')), delimiter=';')
    headers = [
    'PN', 'MODEL', 'Description', 'Qty in stock', 'Price',
    'CONDITION', 'Manufacturer', 'Category', 'Warranty', 'Warranty description',
    'SW', 'KBD', 'DCC Category', 'PN', 'EAN', 'BLUETOOTH', 'RESOLUTION',
    'SCREENSIZE', 'HDD01', 'HDD02', 'MEMORYTOTAL', 'GRAPHICS01', 'GRAPHICS02',
    'PROCESSOR', 'OS', 'WIRELESS', 'DISPLAYTYPE', 'COLOR', 'DCCURL',
    'ETAQUANTITY', 'ETADATE', 'WIN11READY', 'REMOTESTOCK'
    ]
    itemList = []
    for row in csvReader:
        itemDict = {header: (None if value == 'n/a' else value) for header, value in zip(headers, row)}
        itemList.append(itemDict)
    return itemList

async def fetchDataFromUrl(url, session: aiohttp.ClientSession):
    response = await session.get(url)
    if response.status == 200:
        return response
    raise Exception(f"Error fetching data from {url}. Status code: {response.status}")
        
async def loginAndFetchData(url, username, password, loginUrl, session: aiohttp.ClientSession):
    loginResp = await session.post(loginUrl, data={'username': username, 'password': password})
    if loginResp.status == 200:
        return await fetchDataFromUrl(url, session)
    elif loginResp.status == 401:
        raise Exception(f"Invalid credentials for {loginUrl}")
    else:
        raise Exception(f"Error logging in to {loginUrl}. Status code: {loginResp.status}")
    
async def fetchDataFromFtp(url, username, password):
    ftp = FTP()
    ftp.connect(url)
    ftp.login(username, password)
    fileList = ftp.nlst()
    xmlFile = io.BytesIO()
    file = next(iter([file for file in fileList if file.endswith('.xml')]))
    if file:
        ftp.retrbinary(f'RETR {file}', xmlFile.write)
        xmlFile.seek(0)
        data = xmlFile.read()
        return await processXml(None, data)
    return None

#todo: change this so its not hardcoded for foxWay
async def fetchDataFromSftp(url, username, password):
    sshClient = paramiko.SSHClient()
    sshClient.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    sshClient.connect(url, username=username, password=password)
    sftp = sshClient.open_sftp()
    sftp.chdir('/home/knvftp/webapps/knv-ftp')
    fileList = sftp.listdir()
    file = next(iter([file for file in fileList if file.endswith('.csv')]))
    if file:
        csvFile = io.BytesIO()
        sftp.getfo(file, csvFile)
        csvFile.seek(0)
        data = csvFile.read()
        return await processCsv(None, data)
    return 

