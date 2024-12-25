import json
config = None

def init():
    global config
    with open('./config.json') as f:
        config = json.load(f)
    

