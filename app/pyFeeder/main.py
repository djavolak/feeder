import argparse
import asyncio
from hashlib import md5
import config
import traceback
import cache
from logger import logger

async def calculateMd5(text):
    hash = md5()
    hash.update(text.encode('utf-8'))
    return hash.hexdigest()

async def main():
    parser = argparse.ArgumentParser()
    config.init()
    await cache.sessionInit()
    import db
    await db.initEcomHelperPool()
    validActions = {
        "processFields": "Process fields for supplier",
        "processSource": "Process source data for supplier",
        "processParsed": "Create parsed products from source data for supplier",
    }
    parser.add_argument("action", help="The action to perform. Choose from: " + ", ".join(validActions.keys()), choices=validActions.keys())
    parser.add_argument("supplier", help="Code of supplier")
    args = parser.parse_args()
    match args.action:
        case "processFields":
            from src.actions.processFieldsForSupplier import processFields
            try:
                await processFields(args.supplier)
            except Exception as e:
                logger.logger.critical("Error in processing fields: %s", str(e), exc_info=True)
        case "processSource":
            from src.actions.processSourceData import processSourceDataForSupplier
            try:
                await processSourceDataForSupplier(args.supplier)
            except Exception as e:
                logger.logger.critical("Error in processing source data: %s", str(e), exc_info=True)
        case 'processParsed':
            from src.actions.createParsedProductFromSourceData import createProductsForSupplier
            try:
                await createProductsForSupplier(args.supplier)
            except Exception as e:
               logger.critical("Error in creting products from source data: %s", str(e), exc_info=True)
        case _:
            logger.warning("Invalid action: %s", args.action)
            
    await db.closeEcomHelperPool()
    await cache.closeSession()
    
if __name__ == "__main__":
    try:
        asyncio.run(main())
    except RuntimeError as e:
        logger.critical("An runtime error occurred: %s", str(e), exc_info=True)
    except Exception as e:
        logger.critical("An exception occurred: %s", str(e), exc_info=True)
    finally:
        print("Process finished")
        
