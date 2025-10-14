from traceback import print_tb
import requests
import json
# from requests.api import request


def extractEntity(uri):
  parts = uri.split('entity/')
  return(parts[1])

def reduceNoise(text):
    noise = ['\n','“','”','[',']',"'",'(',')','*','\"','.']
    for character in noise:
        text = text.replace(character,' ')
    return text

def flatten(t):
    return [item for sublist in t for item in sublist]

def getEnglishValue(literalTuples):
    response = ''
    for tuple in literalTuples:
        if(tuple.get('lang') == 'en'):
            response = tuple.get('value')
    return(response)

def alreadyVisited(list,uri):
  returnValue = False
  for elem in list:
    if (elem == uri):
      returnValue = True
  return(returnValue)


def parseDBpediaSpotlightResponse(response):
    result = []
    if (response.status_code == requests.codes.ok):
        objJson = response.json()
        candidates = objJson.get('annotation').get('surfaceForm')

        if type(candidates) is list:
            for candidate in candidates: #multiple resulta were found
                result.append(candidate)
        else:
            if type(candidates) is dict: #only one result was found
                result.append(candidates)
    else:
        result = {'error':'error occured while conection with thw API '+response.status_code}
    return result


def getWikidataURL(dbpediaSpotlightResult):
    dbpediaURI = 'http://dbpedia.org/resource/' + dbpediaSpotlightResult.get('resource').get('@uri')
    dbpediaJsonURI = 'http://dbpedia.org/data/' + dbpediaSpotlightResult.get('resource').get('@uri') + '.json'
    response2 = requests.get(dbpediaJsonURI)
    dbpediaObj = response2.json()
    wikidata = {}
    if (response2.status_code == requests.codes.ok):
        dbpediaPredicates = dbpediaObj.get(dbpediaURI)
        dbpediaSameAsPredicates = dbpediaPredicates.get('http://www.w3.org/2002/07/owl#sameAs') 
        if not dbpediaSameAsPredicates is None:
            for value in dbpediaSameAsPredicates:
                strValue = value.get('value')
                if strValue.count('wikidata.org') > 0 :
                    # abstract = getEnglishValue( dbpediaPredicates.get('http://dbpedia.org/ontology/abstract') ) 
                    # label = getEnglishValue( dbpediaPredicates.get('http://www.w3.org/2000/01/rdf-schema#label') ) 
                    print(strValue)
                    wikidata = getWikidataInfo(strValue)
                
    return (wikidata)



def getPersons(text):
    result = []
    url = "https://api.dbpedia-spotlight.org/en/candidates?text="+reduceNoise(text)+"&confidence=0.5&types=DBpedia:Person"
    headers = {
    'accept': 'application/json'
    }

    try:
        response = requests.get(url,headers=headers)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        result = parseDBpediaSpotlightResponse(response)
    
    return result

def getPlaces(text):
    result = []
    url = "https://api.dbpedia-spotlight.org/en/candidates?text="+reduceNoise(text)+"&confidence=0.5&types=DBpedia:Place"
    headers = {
    'accept': 'application/json'
    }

    try:
        response = requests.get(url,headers=headers)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        result = parseDBpediaSpotlightResponse(response)
    
    return result

def getWrittenWorks(text):
    result = []
    url = "https://api.dbpedia-spotlight.org/en/candidates?text="+reduceNoise(text)+"&confidence=0.5&types=DBpedia:WrittenWork"
    headers = {
    'accept': 'application/json'
    }

    try:
        response = requests.get(url,headers=headers)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        result = parseDBpediaSpotlightResponse(response)
    return result

def getAll(text):
    result = []
    url = "https://api.dbpedia-spotlight.org/en/candidates?text="+reduceNoise(text)+"&confidence=0.75"
    headers = {
    'accept': 'application/json'
    }

    try:
        response = requests.get(url,headers=headers)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        result = parseDBpediaSpotlightResponse(response)
    return result

def getWikidataURLs(dbpediaSpotlightResultsArray):
    conceptsArray = []
    if type(dbpediaSpotlightResultsArray) is list:
        visited = []
        for candidate in dbpediaSpotlightResultsArray:
            if type(candidate) is dict:
                dbpediaJsonURI = 'http://dbpedia.org/data/' + candidate.get('resource').get('@uri') + '.json'
                if(not alreadyVisited(visited,dbpediaJsonURI)):
                    visited.append(dbpediaJsonURI)
                    wikidata = getWikidataURL(candidate)
                    conceptsArray.append(wikidata)
    return(conceptsArray)


def isNewItem(list, new_item):
    result = True
    for item in list:
        if(item['url'] == new_item['url']):
            result = False;
    return result

def wikidataLookup(text):
    sparqlQuery = """  
        SELECT distinct ?item ?itemLabel ?itemDescription ?instanceOf WHERE{  
        ?item ?label \""""+text+"""\" @en. 
        ?item wdt:P31 ?instanceOf. 
        ?article schema:about ?item .
        ?article schema:inLanguage "en" .
        ?article schema:isPartOf <https://en.wikipedia.org/>.	
        SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }    
        }
        """
    url = 'https://query.wikidata.org/sparql?query='+sparqlQuery
    headers = {
    'accept': 'application/json'
    }

    result = []
    try:
        response = requests.get(url,headers=headers)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        objJson = response.json()
        candidates = objJson.get('results').get('bindings')
        for candidate in candidates:
            label = candidate.get('itemLabel').get('value')
            uri = candidate.get('item').get('value')
            description = candidate.get('itemDescription').get('value')
            instanceOf = candidate.get('instanceOf').get('value')
            type = getType(instanceOf)
            item = {
                'url':uri,
                'description':description,
                'label':label,
                'instanceOf':instanceOf,
                'type':type
            }
            
            if (isNewItem(result, item)):
                result.append(item) 
    return result

def getType(wikidataURL):
    type = {'type':'other','label':'no label'}
    
    #check if is human
    personsFile = open('Persons.json', encoding='utf-8')
    personsList = json.load(personsFile)
    personsFile.close()
    for element in personsList:
        if(element['uri'] == wikidataURL):
                type = {'type':'person','label':element['label']}

    if(type['type'] == 'other'):
        writtenWorkfile = open('WrittenWork.json',encoding="utf8")
        writtenWorkList = json.load(writtenWorkfile)
        writtenWorkfile.close()
        for element in writtenWorkList:
            if(element['uri'] == wikidataURL):
                type = {'type':'written-work','label':element['label']}
        
        if (type['type'] == 'other'):
            placesFile = open('Places.json',encoding='utf8')
            placesList = json.load(placesFile)
            placesFile.close()
            for element in placesList:
                if(element['uri'] == wikidataURL):
                    type = {'type':'place', 'label':element['label']}


    return (type)


def getWikidataInfo(arg):
    result = ''
    if('http://www.wikidata.org/entity/' in arg):
        wikidataURL = arg
        Qcode = extractEntity(arg)
    else:
        wikidataURL = 'http://www.wikidata.org/entity/'+arg
        Qcode = arg
    try:
        response = requests.get(wikidataURL)       
    except requests.exceptions.RequestException as e:  # This is the correct syntax
        return {'error':'something went wrong connecting with dbpedia spotlight'}
    else:
        objJson = response.json()
        if("P31" in objJson['entities'][Qcode]['claims']):
            instanceOfProperty = objJson['entities'][Qcode]['claims']['P31']
            if instanceOfProperty:
                instanceOf = instanceOfProperty[0]['mainsnak']['datavalue']['value']['id']
                type = getType('http://www.wikidata.org/entity/'+instanceOf)
                for instance in instanceOfProperty:
                        temp = getType('http://www.wikidata.org/entity/'+instance['mainsnak']['datavalue']['value']['id'])
                        if (temp['type'] != 'other'):
                            instanceOf = instance['mainsnak']['datavalue']['value']['id']
                            type = temp
                else:
                    instanceOf = ''
                description = ''
                if('en' in objJson['entities'][Qcode]['descriptions']):
                    description = objJson['entities'][Qcode]['descriptions']['en']['value']
                label = ''
                if('en' in objJson['entities'][Qcode]['labels']):
                    label = objJson['entities'][Qcode]['labels']['en']['value']
                
                result = {
                    'url': 'http://www.wikidata.org/entity/'+Qcode,
                    'description':description,
                    'label':label,
                    'instanceOf':instanceOf,
                    'type':type
                }
 
    return result



#----------------------------------------------------------------------------------------------------------------------------
# ----------------------- FLASK APPLICATION ---------------------------------------------------------------------------------
#----------------------------------------------------------------------------------------------------------------------------

import flask
from markupsafe import escape

app = flask.Flask(__name__)
@app.route('/')
def index():
    return '<p>Help info</p>'

@app.route("/getPlaces/<text>")
def get_places(text):
    candidates = getPlaces(text)
    if('error' in candidates):
        resp = flask.Response( json.dumps(candidates))
    else:
        wikidata = getWikidataURLs(candidates)
        resp = flask.Response( json.dumps(wikidata) )
    
    resp.headers['Content-Type'] = 'application/json'
    return resp

@app.route("/getPersons/<text>")
def get_persons(text):    
    candidates = getPersons(text)
    
    if('error' in candidates):
        resp = flask.Response( json.dumps(candidates))
    else:
        wikidata = getWikidataURLs(candidates)
        resp = flask.Response( json.dumps(wikidata) )
    
    resp.headers['Content-Type'] = 'application/json'
    return resp

@app.route("/getWrittenWorks/<text>")
def get_written_works(text):
    candidates = getWrittenWorks(text)
    if('error' in candidates):
        resp = flask.Response( json.dumps(candidates))
    else:
        wikidata = getWikidataURLs(candidates)
        resp = flask.Response( json.dumps(wikidata) )
    
    resp.headers['Content-Type'] = 'application/json'
    return resp

@app.route("/getAll/<text>")
def get_all(text):
    candidates = getAll(text)
    if('error' in candidates):
        resp = flask.Response( json.dumps(candidates))
    else:
        wikidata = getWikidataURLs(candidates)
        resp = flask.Response( json.dumps(wikidata) )
    
    resp.headers['Content-Type'] = 'application/json'
    return resp


@app.route("/wikidataLookup/<text>")
def wikidata_lookup(text):
    result = wikidataLookup(text)
    resp = flask.Response( json.dumps(result) )
    resp.headers['Content-Type'] = 'application/json'
    return resp

@app.route("/getInfo/<text>")
def get_info(text):
    result = getWikidataInfo(text)
    resp = flask.Response( json.dumps(result) )
    resp.headers['Content-Type'] = 'application/json'
    resp.headers['Access-Control-Allow-Origin'] = '*'
    return resp


@app.route("/test/<text>")
def test(text):
    result = getType('https://www.wikidata.org/entity/Q515')
    resp = flask.Response( json.dumps(result) )
    resp.headers['Content-Type'] = 'application/json'
    return resp

