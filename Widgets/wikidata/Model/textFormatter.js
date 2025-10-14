//---------------------------------------------------------------------------
//--------------------- Utilitarian Functions -------------------------------
//---------------------------------------------------------------------------
function prepareText(in_text){
    let result = in_text;
    const noise = ['/','*','(',')','&','#',"'",'"'];

    noise.forEach(char => {
        result = result.replaceAll(char,' ');
    });

    return result;
}


async function endpointHandler(in_method = '',in_argument = ''){
    //url ='Model/wrapper.php?method='+ method + '&argument='+prepareText(argument);
    const url = 'Model/wrapper.php';
    let body = {
        'method':in_method,
        'argument':in_argument
    };
    const response = await fetch(url,{
            method: 'POST',
            mode:'no-cors', 
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'},
            body: JSON.stringify(body)
    });
    try {
        return await response.json();
    } catch (error) {
        console.log(error);
        console.log("error doing: "+in_method+" with argument: "+in_argument);
        return undefined; 
    }
    
}

function extractEntityID(wikidataURL){
    const tokens = wikidataURL.split('/');
    return tokens.pop();
}

//copy-pasted from: https://www.tutorialspoint.com/levenshtein-distance-in-javascript
const levenshteinDistance = (str1 = '', str2 = '') => {
    //creates the matrix, |str2|x|str1|
    const track = Array(str2.length + 1).fill(null).map( () => Array(str1.length + 1).fill(null) );

    //fill the first column
    for (let i = 0; i <= str1.length; i += 1) {
       track[0][i] = i;
    }
    //fill the first row
    for (let j = 0; j <= str2.length; j += 1) {
       track[j][0] = j;
    }

    //rest of the matrix
    for (let j = 1; j <= str2.length; j += 1) {
       for (let i = 1; i <= str1.length; i += 1) {
          const indicator = str1[i - 1] === str2[j - 1] ? 0 : 1;
          track[j][i] = Math.min(
             track[j][i - 1] + 1, // deletion
             track[j - 1][i] + 1, // insertion
             track[j - 1][i - 1] + indicator, // substitution
          );
       }
    }
    return track[str2.length][str1.length];
 };

 function findClosestToken(tokens,label){
    //let's try to find an exact match first
    const fulltext = tokens.join(' ');
    const fulltextLC = fulltext.toLowerCase();
    const labelLC = label.toLowerCase();
    const pos = fulltextLC.search(labelLC);

    if(pos >= 0){ //exact match was found
        result = fulltext.substr(pos,label.length);
    }else{ // no exact match, let's look for the closed word
        result = '';
        min = 10000;
        for(let i = 0; i < tokens.length; i++){
        distance = levenshteinDistance(tokens[i],label);
        if(distance < min){
            result = tokens[i];
            min = distance;
        }
        }
    }

    return(result) ; 
 }
//---------------------------------------------------------------------------
//--------------------- Endpoint Interaction Functions -----------------------
//---------------------------------------------------------------------------

// Generates a list of enties from an annotated text
async function loadFromText( in_text ){
    //reset the entities list
    GLOBAL_entities = [];

    GLOBAL_textAnnotated = in_text; //set the global variable

    clean = in_text.replaceAll('https','http');
    clean = clean.replaceAll('[',' ');
    clean = clean.replaceAll(']',' ');
    GLOBAL_textClean = clean;

    var annotations = clean.match(/\bhttp?:\/\/\S+/gi);
    if(annotations != null){
        annotations.forEach(annotation => {
            GLOBAL_textClean = GLOBAL_textClean.replaceAll(annotation,'')
            //get the entity informartion and remove it from the text
    
            endpointHandler('getInfo',extractEntityID(annotation))
                .then(data=>{
                    GLOBAL_entities.push(data);
                    refreshEntities();
                });
    
        });
    }  
}

function annotateText(){
    /*
    tokenize text
    for each entity:
        find the token closest to the label
        susbtitute on the result text token with token[uri]
    */

    updateGlobalVariables();
    var tokens = GLOBAL_textClean.split(' ');
    GLOBAL_textAnnotated = GLOBAL_textClean;
    GLOBAL_entities.forEach(entity =>{
        //console.log(entity);
        if(entity != undefined){
            var contextToken = findClosestToken(tokens,entity['label']);

            GLOBAL_textAnnotated = GLOBAL_textAnnotated.replace(contextToken,contextToken+' ['+entity['url']+'] ')
        }
    });
    
    refreshText();
}

function removeEntity(in_url){

    for(let i = 0; i < GLOBAL_entities.length; i++){
        if(GLOBAL_entities[i] != undefined){
            if(GLOBAL_entities[i]['url'] == in_url){
            GLOBAL_entities.splice(i,1);
        }
        }
        
    }
    refreshEntities();

}

function addEntity(in_url){
    var entity = '';
    GLOBAL_lookupResults.forEach(entity => {
        if(entity['url'] == in_url){
            GLOBAL_entities.push(entity);
        }
    });
    markAsAdded(in_url);
    refreshEntities();
}

async function findPersons(){
    data = await endpointHandler('getPersons',GLOBAL_textClean);
    if(data != undefined){
        data.forEach(entity =>{
            GLOBAL_entities.push(entity);
        });
        refreshEntities();
    }
    
}

async function findPlaces(){
    data = await endpointHandler('getPlaces',GLOBAL_textClean);
    if(data != undefined){
        data.forEach(entity =>{
            GLOBAL_entities.push(entity);
        });
        refreshEntities();
    }
}

async function findWrittenWork(){
    data = await endpointHandler('getWrittenWorks',GLOBAL_textClean);
    if(data != undefined){
        data.forEach(entity =>{
            GLOBAL_entities.push(entity);
        });
        refreshEntities();
    }
}


async function findAll(){
    data = await endpointHandler('getAll',GLOBAL_textClean);
    if(data != undefined){
        data.forEach(entity =>{
            GLOBAL_entities.push(entity);
        });
        refreshEntities();
    }
}

async function lookup(query){
    data = await endpointHandler('wikidataLookup',query);
    if(data != undefined){
        GLOBAL_lookupResults = [];
        data.forEach(entity=>{
            GLOBAL_lookupResults.push(entity);
        });

        refreshResults();
    }
}

//---------------------------------------------------------------------------
//--------------------- UX Functions ----------------------------------------
//---------------------------------------------------------------------------

function updateGlobalVariables(){
    GLOBAL_textAnnotated = document.querySelector('#textContainer').value;
    clean = GLOBAL_textAnnotated.replaceAll('https','http');
    clean = clean.replaceAll('[','');
    clean = clean.replaceAll(']','');
    GLOBAL_textClean = clean;
    
    var annotations = clean.match(/\bhttp?:\/\/\S+/gi);
    if(annotations != null){
        annotations.forEach(annotation => {
            GLOBAL_textClean = GLOBAL_textClean.replaceAll(annotation,'');
        })
    }
    
}

function refreshText(){   
    var textContainer = document.querySelector('#textContainer');
    textContainer.value = GLOBAL_textAnnotated;
}

function cleanTabContainers(){
    var containerPersons = document.querySelector('#persons-tab-entities');
    containerPersons.innerHTML = '';
    var counterPersons = document.querySelector('#persons-counter');
    counterPersons.innerHTML = '';

    var containerPlaces = document.querySelector('#places-tab-entities');
    containerPlaces.innerHTML = '';
    var counterPlaces = document.querySelector('#places-counter');
    counterPlaces.innerHTML = '';

    var containerWW = document.querySelector('#ww-tab-entities');
    containerWW.innerHTML = '';
    var counterWW = document.querySelector('#ww-counter');
    counterWW.innerHTML = '';

    var containerOther = document.querySelector('#other-tab-entities');
    containerOther.innerHTML = '';
    var counterOthers = document.querySelector('#others-counter');
    counterOthers.innerHTML = '';
}


function refreshEntities(){
    var template = document.querySelector('#entity-card-template');
    cleanTabContainers();

    var personsCount = 0;
    var placesCount = 0;
    var wwCount = 0;
    var othersCount = 0;


    GLOBAL_entities.forEach(entity =>{  
        
        if(entity != undefined){  
            var newCard = template.content.cloneNode(true);

            newCard.querySelector('#label').textContent = entity['label'];
            newCard.querySelector('#qcode').textContent = entity['url'];
            newCard.querySelector('#description').textContent = entity['description'];
            newCard.querySelector('#remove-btn').setAttribute('onclick','removeEntity("'+entity['url']+'")');
        
            var container = false;
        
            if(entity['type']['type'] == 'person'){
                container = document.querySelector('#persons-tab-entities');
                personsCount++;
            }else if(entity['type']['type'] == 'place'){
                container = document.querySelector('#places-tab-entities');
                placesCount++;
    
            }else if(entity['type']['type'] == 'written-work'){
                container = document.querySelector('#ww-tab-entities');
                wwCount++;
            }else{
                container = document.querySelector('#other-tab-entities');
                othersCount++;
            }     
            container.appendChild(newCard);
        }
        
    });

    document.querySelector('#persons-counter').textContent = '('+personsCount+')';
    document.querySelector('#places-counter').textContent = '('+placesCount+')';
    document.querySelector('#ww-counter').textContent = '('+wwCount+')';
    document.querySelector('#others-counter').textContent = '('+othersCount+')';

}

function refreshResults(){
    //console.log(GLOBAL_lookupResults);
    var modalLookupResults = document.querySelector('#lookupModal-results');
    modalLookupResults.innerHTML = '';

    document.querySelector('#lookup-spinner').classList.add("visually-hidden");
    var template = document.querySelector('#lookup-result').content;

    GLOBAL_lookupResults.forEach(entity => {
        var newResult = template.cloneNode(true);
        newResult.querySelector('#lookup-title').textContent = entity['label'];
        newResult.querySelector('#lookup-description').textContent = entity['description'];
        newResult.querySelector('#lookup-url').textContent = entity['url'];
        newResult.querySelector('#add-btn').setAttribute('onclick','addEntity("'+entity['url']+'")');
        newResult.querySelector('#add-btn').setAttribute('name',entity['url']);
        modalLookupResults.appendChild(newResult);
    });
}

function markAsAdded(in_url){
    var addBtn = document.querySelector('[name="'+in_url+'"]');
    addBtn.setAttribute('onclick','');
    addBtn.classList.remove('btn-primary');
    addBtn.textContent = 'Added'
}
