"use strict";

let submits = document.querySelectorAll('.submit'); // получение всех кнопок отправки формы
let forms = document.querySelectorAll('form');  // получение всех форм


let params = window
    .location
    .search
    .replace('?','')
    .split('&');
console.log(params);

if(params.length > 0) {
    for (let i = 0; i < params.length; i++) {
        let param = params[i].replace(/%5B%5D/i, '[]').split('=');
        console.log(param);
        addFilterItems(param);
    }
}


function addFilterItems(param) {
    for (let i = 0; i < forms.length; i++) {
        if(forms[i].name.match(param[0].slice(1,-2))) {
            console.log(forms[i].name);
        } else {
            let elementAdd = document.createElement('input');
            elementAdd.setAttribute('type', 'hidden');
            elementAdd.setAttribute('name', param[0]);
            elementAdd.setAttribute('value', param[1]);
            forms[i].prepend(elementAdd);
        }
        
    }        
}
