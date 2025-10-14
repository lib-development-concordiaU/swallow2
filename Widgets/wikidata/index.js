function loadTextHadler(){
    const text = document.querySelector('#textContainer').value;
    var spinner = document.querySelector('#middle-spinner').cloneNode(true);
    spinner.id = 'middle-spinner-clone';
    var containerPersons = document.querySelector('#persons-tab-entities');
    containerPersons.innerHTML = '';
    
    containerPersons.appendChild(spinner);
    spinner.classList.remove('visually-hidden');
    
    loadFromText(text);
}

function refreshTextAnnotationsHandler(){
    annotateText();
}

function findPersonsHandler(){
    if(GLOBAL_textClean != ''){
        var spinner = document.querySelector('#find-person-spinner');
        spinner.classList.remove('visually-hidden');
        findPersons().then( ()=>{
            spinner.classList.add('visually-hidden');
        });
    }else{
        alert('Please load the text before looking for entities');
    } 
}

function findPlacesHandler(){
    if(GLOBAL_textClean != ''){
        var spinner = document.querySelector('#find-places-spinner');
        spinner.classList.remove('visually-hidden');
        findPlaces().then( ()=>{
            spinner.classList.add('visually-hidden');
        });
    }else{
        alert('Please load the text before looking for entities');
    } 
}

function findWWHandler(){
    if(GLOBAL_textClean != ''){
        var spinner = document.querySelector('#find-ww-spinner');
        spinner.classList.remove('visually-hidden');
        findWrittenWork().then( ()=>{
            spinner.classList.add('visually-hidden');
        });
    }else{
        alert('Please load the text before looking for entities');
    } 
}

function findAllHandler(){
    if(GLOBAL_textClean != ''){
        var spinner = document.querySelector('#find-all-spinner');
        spinner.classList.remove('visually-hidden');
        findAll().then( ()=>{
            spinner.classList.add('visually-hidden');
        });
    }else{
        alert('Please load the text before looking for entities');
    } 
}

function lookupHandler(){
    var query = document.querySelector('#lookup-query').value;
    document.querySelector('#lookup-spinner').classList.remove("visually-hidden");
    lookup(query).then( ()=>{
        console.log('done');
    });
}