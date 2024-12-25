import aiomysql
from config import config
from functools import wraps
from pymysql import OperationalError
import asyncio
from logger import logger

dbSettingsEcomHelper = {}
poolEcomhelper = None
dbSettingsEcomHelper['autocommit'] = True
dbSettingsEcomHelper['cursorclass'] = aiomysql.DictCursor
dbSettingsEcomHelper['host'] = config['db']['ecomHelper']['host']
dbSettingsEcomHelper['port'] = config['db']['ecomHelper']['port']
dbSettingsEcomHelper['user'] = config['db']['ecomHelper']['user']
dbSettingsEcomHelper['password'] = config['db']['ecomHelper']['password']
dbSettingsEcomHelper['db'] = config['db']['ecomHelper']['db']
dbSettingsEcomHelper['charset'] = config['db']['ecomHelper']['charset']

async def initEcomHelperPool():
    global poolEcomhelper
    poolEcomhelper = await aiomysql.create_pool(**dbSettingsEcomHelper)

async def closeEcomHelperPool():
    global poolEcomhelper
    if poolEcomhelper:
        poolEcomhelper.close()
        await poolEcomhelper.wait_closed()

def retryOnDeadlok(maxRetries=3, waitSeconds=1, logDeadlockInfo=False):
        def decorator(func):
            @wraps(func)
            async def wrapper(*args, **kwargs):
                retries = 0
                while retries < maxRetries:
                    try:
                        return await func(*args, **kwargs)
                    except OperationalError as e:
                        if e.args[0] == 1213:
                            logger.warning(f"Deadlock detected ({retries}/{maxRetries}). Retrying in {waitSeconds} seconds. (Function: {func.__name__})")
                            if logDeadlockInfo:
                                logDedalockInfo(func, args, kwargs, e)
                            await asyncio.sleep(waitSeconds)
                            retries += 1
                        else:
                            raise
                raise Exception(f"Max retries ({maxRetries}) reached. Unable to complete operation.")

            return wrapper
        def logDedalockInfo(func, args, kwargs, exception):
            logger.info(
                "Logging deadlock information:\n"
                "Function: %s\n"
                "Arguments: %s\n"
                "Keyword Arguments: %s\n"
                "Exception: %s\n"
                "----------------------------",
                func.__name__, args, kwargs, exception
            )
        return decorator