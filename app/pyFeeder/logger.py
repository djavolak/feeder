import logging
import os
from datetime import datetime

def initLogger():
    global logger
    logger = logging.getLogger('feeder')
    logger.setLevel(logging.DEBUG)
    formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s', datefmt='%d-%m-%Y %H:%M:%S')
    baseLogPath = os.path.abspath(os.path.join(os.getcwd(), os.pardir))
    logDir = os.path.join(baseLogPath, 'logs')
    if not os.path.exists(logDir):
        os.makedirs(logDir)
    currentMonth = datetime.now().strftime('%Y-%m')
    monthDir = os.path.join(logDir, currentMonth)
    if not os.path.exists(monthDir):
        os.makedirs(monthDir)
    logFileName = datetime.now().strftime('%d-%m-%Y.log')
    logFilePath = os.path.join(monthDir, logFileName)
    fileHandler = logging.FileHandler(logFilePath,encoding='utf-8')
    fileHandler.setLevel(logging.ERROR)
    fileHandler.setFormatter(formatter)
    logger.addHandler(fileHandler)
    consoleHandler = logging.StreamHandler()
    consoleHandler.setLevel(logging.DEBUG)
    consoleHandler.setFormatter(formatter)
    logger.addHandler(consoleHandler)
    
    return logger

logger = initLogger()