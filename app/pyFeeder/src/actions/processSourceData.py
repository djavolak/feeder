import src.supplier.service as SupplierService
import src.crudUtils as crud
import db
import json
import uuid
import asyncio
async def processSourceDataForSupplier(supplierCode):
    batchSize = 300
    tasks = []
    result = await crud.fetchBy('supplier', 'code', supplierCode, db.poolEcomhelper)
    if len(result) == 0:
        raise Exception(f"Supplier with code {supplierCode} not found")
    supplier = result[0]
    products = await SupplierService.fetchProductsForSupplier(supplier)
    mappedFields = await SupplierService.getProductFieldMapping(supplier.get('id'))
    for i in range(0, len(products), batchSize):
        productForDbList = []
        batch = products[i:i + batchSize]
        for product in batch:
            productForDb = {
                "productData": json.dumps(product, indent=2, ensure_ascii=False),
                "supplierId": supplier.get('id'),
                "cat1": product.get(mappedFields['cat1']),
                "cat2": product.get(mappedFields['cat2']),
                "cat3": product.get(mappedFields['cat3']),
                "supplierProductId": product.get(mappedFields['sku']),
                "id": str(uuid.uuid4())
            }
            productForDbList.append(productForDb)
        tasks.append(crud.insertMany('sourceProduct', productForDbList, db.poolEcomhelper, duplicateKeyUpdate=True))
    await asyncio.gather(*tasks)
