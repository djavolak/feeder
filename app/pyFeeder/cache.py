from aiohttp_client_cache import CachedSession, RedisBackend
session = None

async def sessionInit():
    global session
    session = CachedSession(backend=RedisBackend())
    return session

async def closeSession():
    global session
    await session.close()