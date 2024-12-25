from sre_compile import isstring
import traceback
import src.crudUtils as crud
import db
import src.supplier.service as SupplierService
import json
import asyncio
import uuid
import os
import aiohttp
from config import config
import aiofiles
from logger import logger
async def createProductsForSupplier(supplierCode):
    result = await crud.fetchBy('supplier', 'code', supplierCode, db.poolEcomhelper)
    if len(result) == 0:
        raise Exception(f"Supplier with code {supplierCode} not found")
    
    supplier = result[0]
    fieldMapping = await SupplierService.getProductFieldMapping(supplier.get('id'))
    async with aiohttp.ClientSession() as session:
        await iterateThroughProductsAndCreateTasks(fieldMapping, session, supplier.get('id'))

async def iterateThroughProductsAndCreateTasks(fieldMapping, session, supplierId):
    cursorValue = None
    pageSize = 1000
    tasks = []
    try:
        while True:
            pageData = await crud.fetchPageBy('sourceProduct', pageSize, db.poolEcomhelper, 'supplierId', supplierId, cursorValue)
            if not pageData:
                break
            cursorValue = pageData[-1]['id']
            tasks.append(createProducts(pageData, fieldMapping, session))
        await asyncio.gather(*tasks)
    except Exception as e:
        logger.critical("Error in fetching producst from database: %s", str(e), exc_info=True)

def generateProductData(product, fieldMapping):
    sourceData = json.loads(product['productData'])
    productData = dict()
    attributes = None

    supplierId = product.get('supplierId')
    productId = product.get('id')

    for dbColumn, map in fieldMapping.items():
        if map is not None:
            if dbColumn == 'meta':
                continue
            if dbColumn == 'attributes':
                attributes = sourceData.get(map, None)
                continue
            productData[dbColumn] = sourceData.get(map, None)
        else:
            productData[dbColumn] = None

    if 'meta' in fieldMapping:
        metaFields = fieldMapping.get('meta')
        meta = dict()
        productData['meta'] = ''
        if metaFields is not None:
            for metaField in metaFields:
                meta[metaField] = sourceData.get(metaField)
            productData['meta'] = json.dumps(meta, ensure_ascii=False)

    productData['id'] = str(uuid.uuid4())
    productData['supplierId'] = supplierId
    productData['sourceProductId'] = productId

    if attributes is not None:
        productData['attributes'] = json.dumps(attributes, ensure_ascii=False)

    cat1 = product.get('cat1')
    cat2 = product.get('cat2')
    cat3 = product.get('cat3')
    categories = [cat for cat in [cat1, cat2, cat3] if cat is not None]
    productData['supplierCategory'] = ' - '.join(categories)

    return productData

async def createProducts(products, fieldMapping, session):
    productDataList = []
    imagesToSave = []
    for product in products:
        productForDb = generateProductData(product, fieldMapping)
        productDataList.append(productForDb)
        images = productForDb.pop('images', None)
        if images is not None or (isstring(images) and images.strip()):
            imageData = await processProductImages(images, productForDb['sourceProductId'], session)
            if imageData is not None:
                imagesToSave.extend(imageData)  
    try:
        if len(productDataList) > 0:
            await asyncio.create_task(crud.insertMany('parsedProduct', productDataList, db.poolEcomhelper, True))
        if len(imagesToSave) > 0:
            await asyncio.create_task(crud.insertMany('sourceProductImage', imagesToSave, db.poolEcomhelper, True))
    except Exception as e:
        logger.critical("Error in inserting products into database: %s", str(e), exc_info=True)

async def processProductImages(images, productId, session):
    tasks = []
    if isinstance(images, str) and images.strip():
         tasks.append(downloadImage(session, images, productId, True))
    elif isinstance(images, dict):
        for key, value in images.items():
            if isinstance(value, list) and all(isinstance(url, str) for url in value):
                if key == '0':
                     tasks.append(downloadImage(session, value[0], productId, True))
                else:
                    tasks.append(downloadImage(session, value[0], productId, False))
    try:
        images = await asyncio.gather(*tasks)
        return images
    except Exception as e:
        logger.debug("Error in downloading images: %s", str(e), exc_info=True)

async def downloadImage(session, imageUrl, productId, main):
    try:
        imageFilename = await crud.calculateMd5(imageUrl + productId) + '.jpg'
        imageFromDb = await crud.fetchBy('sourceProductImage', 'fileName', imageFilename, db.poolEcomhelper)
        if not imageFromDb:
            imagesFolder = config['imagePath']
            savePath = os.path.join(imagesFolder, imageFilename)
            if not os.path.exists(imagesFolder):
                os.makedirs(imagesFolder)
            async with session.get(imageUrl) as response:
                if response.status == 200:
                    imageContent = await response.read()
                    async with aiofiles.open(savePath, 'wb') as file:
                        await file.write(imageContent)
                    return {
                        "id": str(uuid.uuid4()),
                        "fileName": imageFilename,
                        "parsedProductId": productId,
                        "main": main
                    }
                else:
                    logger.debug("Error in downloading image: %s", imageUrl)
    except Exception as e:
        logger.debug("Error in downloading image: %s", str(e), exc_info=True)

    return None


