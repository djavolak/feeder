from src.supplier import service as SupplierService
import json
import src.crudUtils as crud
import db
import uuid

async def processFields(supplierCode):
    supplier = await crud.fetchBy('supplier', 'code', supplierCode, db.poolEcomhelper)
    if (len(supplier) == 0):
        raise Exception(f"Supplier not found for code {supplierCode}")
    supplier = supplier[0]
    fields = await fetchFields(supplier)
    for field in fields:
        columns = ['supplierId', 'sourceFieldName']
        values = [supplier.get('id'), field]
        dbField = await crud.fetchByMultipleColumnns('supplierFieldsMapping', columns, values, db.poolEcomhelper)
        if not dbField:
            await crud.insert('supplierFieldsMapping', {
                'supplierId': supplier.get('id'),
                'sourceFieldName': field,
                'id': str(uuid.uuid4())
            }, db.poolEcomhelper)

async def fetchFields(supplier):
    products = await SupplierService.fetchProductsForSupplier(supplier)               
    return extractFieldsFromProducts(products)

def extractFieldsFromProducts(products):
    uniqueFields = set()
    for product in products:
        uniqueFields.update(product.keys())
    return list(uniqueFields)


