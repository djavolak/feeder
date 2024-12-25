from hashlib import md5
from db import retryOnDeadlok
async def fetchBy(tableName, columnName, value, pool):
    async with pool.acquire() as conn:
        sql = f"SELECT * FROM {tableName} WHERE {columnName} = %s"
        async with conn.cursor() as cur:
            await cur.execute(sql, (value))
            row = await cur.fetchall()
            return row
        
async def fetchByMultipleColumnns(tableName, columns, values, pool):
    async with pool.acquire() as conn:
        sql = f"SELECT * FROM {tableName} WHERE {' AND '.join([f'{column} = %s' for column in columns])}"
        async with conn.cursor() as cur:
            await cur.execute(sql, tuple(values))
            row = await cur.fetchall()
            return row
    
async def fetchPage(tableName, pageSize, pool, cursorValue=None):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            if cursorValue:
                await cursor.execute(
                    f"SELECT * FROM {tableName} WHERE id > %s ORDER BY id LIMIT %s",
                    (cursorValue, pageSize),
                )
                return await cursor.fetchall()

            await cursor.execute(
                f"SELECT * FROM {tableName} ORDER BY id LIMIT %s",
                (pageSize,),
            )
            return await cursor.fetchall()
    
async def fetchPageByMultipleColumns(tableName, pageSize, pool, currsorValue = None, columns = None, values = None):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            if(currsorValue):
                sql = f"SELECT * FROM {tableName} WHERE id > %s AND {' AND '.join([f'{column} = %s' for column in columns])} ORDER BY id LIMIT %s"
                await cursor.execute(sql, (currsorValue, tuple(values), pageSize))
                return await cursor.fetchall()
            sql = f"SELECT * FROM {tableName} WHERE {' AND '.join([f'{column} = %s' for column in columns])} ORDER BY id LIMIT %s"
            await cursor.execute(sql, (tuple(values), pageSize))
            return await cursor.fetchall()
        
async def fetchPageBy(tableName, pageSize, pool, columnName, value, cursorValue=None):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            if cursorValue:
                await cursor.execute(
                    f"SELECT * FROM {tableName} WHERE id > %s AND {columnName} = %s ORDER BY id LIMIT %s",
                    (cursorValue, value, pageSize),
                )
                return await cursor.fetchall()

            await cursor.execute(
                f"SELECT * FROM {tableName} WHERE {columnName} = %s ORDER BY id LIMIT %s",
                (value, pageSize),
            )
            return await cursor.fetchall()
        
async def fetchAll(tableName, pool):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute(
                f"SELECT * FROM {tableName}",
            )
            return await cursor.fetchall()

async def insert(tableName, data, pool, duplicateKeyUpdate=None):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            columns = ','.join(data.keys())
            values = ','.join(['%s' for _ in data.keys()])
            sql = f"INSERT INTO {tableName} ({columns}) VALUES ({values})"
            
            if duplicateKeyUpdate:
                # Exclude 'id' from the update statement so that it doesn't get updated
                updateColumns = [f"{key} = {key}" for key in data.keys() if key != 'id']
                update_sql = f" ON DUPLICATE KEY UPDATE {', '.join(updateColumns)}"
                sql += update_sql

            await cursor.execute(sql, tuple(data.values()))
            return cursor.lastrowid

@retryOnDeadlok(10, 2, False)
async def insertMany(tableName, dataList, pool, duplicateKeyUpdate=None):
    if len(dataList) == 0:
        return
    if dataList[0] is None:
        return
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            columns = ','.join(dataList[0].keys())
            values = ','.join(['%s' for _ in dataList[0].keys()])
            sql = f"INSERT INTO {tableName} ({columns}) VALUES ({values})"
            if duplicateKeyUpdate:
                updateColumns = [f"{key} = {key}" for key in dataList[0].keys() if key != 'id']
                update_sql = f" ON DUPLICATE KEY UPDATE {', '.join(updateColumns)}"
                sql += update_sql
            await cursor.executemany(sql, [tuple(data.values()) for data in dataList if data is not None])
            return cursor.lastrowid

async def update(tableName, data, id, pool):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            sql = f"UPDATE {tableName} SET {','.join([f'{key}=%s' for key in data.keys()])} WHERE id = %s"
            await cursor.execute(sql, tuple(data.values()) + (id,))
            return cursor.lastrowid

async def updateByField(tableName, data, field: dict, pool):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            field_key, field_value = list(field.items())[0]
            sql = f"UPDATE {tableName} SET {','.join([f'{key}=%s' for key in data.keys()])} WHERE {field_key} = %s"
            await cursor.execute(sql, tuple(data.values()) + (field_value,))
            return cursor.lastrowid
        
async def delete(tableName, id, pool):
    async with pool.acquire() as conn:
        async with conn.cursor() as cursor:
            sql = f"DELETE FROM {tableName} WHERE id = %s"
            await cursor.execute(sql, (id,))
            return cursor.lastrowid

async def calculateMd5(text):
    hash = md5()
    hash.update(text.encode('utf-8'))
    return hash.hexdigest()
        
        