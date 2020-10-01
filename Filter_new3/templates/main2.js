"use strict";

let forms = document.querySelectorAll('form');  // получение всех форм

// let ratio = {
//     'sort1': 'form_sort1',
//     'sort2': 'form_sort2',
//     'price_start': 'form_price',
//     'price_end': 'form_price',
//     'color[]': 'form_color',
//     'size[]': 'form_size',
//     'textile[]': 'form_textile',
//     'style[]': 'form_style',
//     'line[]': 'form_line',
//     'season[]': 'form_season',
//     'pattern[]': 'form_pattern',
//     'fashion[]': 'form_fashion',
//     'details[]': 'form_details'
// }


let params = window
    .location
    .search
    .replace('?','')
    .split('&');
    

console.log(params);

if(params.length > 0 && params[0] != "") {
    for (let i = 0; i < params.length; i++) {
        let param = decodeURI(params[i]).split('=');
        console.log(param);
        addFilterItems(param);
    }
}

// function addFilterItems(param) {
//     for (let i = 0; i < forms.length; i++) {
//         if(forms[i].name.match(param[0].slice(0,4))) {
//             for (let j = 0; j < forms[i].elements.length; j++) {
//                 if (forms[i].elements[j].value == param[1]) {
//                     forms[i].elements[j].setAttribute('checked', 'checked');
//                 } 
//             }
//             if(forms[i].name == 'form_price') {
//                 for (let j = 0; j < forms[i].elements.length; j++) {
//                     if (forms[i].elements[j].name == param[0]) {
//                         forms[i].elements[j].value = param[1];
//                     }
//                 }
//             } 
//             if(forms[i].name == 'form_sort1' || forms[i].name == 'form_sort2') {
//                 if (forms[i].elements[1].name == param[0]) {
//                     console.log(forms[i].elements[1].options);
//                     for (let j = 0; j < forms[i].elements[1].options.length; j++) {
//                         let select = forms[i].elements[1].options[j]
//                         if(select.value == param[1]) {
//                             console.log('ok');
//                             select.selected = true;;
//                             forms[i].elements[1].insertBefore(select, select.parentNode.firstChild);
//                         }
//                     }
//                 }
//             }
//         } else {
//             let elementAdd = document.createElement('input');
//             elementAdd.setAttribute('type', 'hidden');
//             elementAdd.setAttribute('name', param[0]);
//             elementAdd.setAttribute('value', param[1]);
//             forms[i].prepend(elementAdd);
//         }         
//     }        
// }


function addFilterItems(param) {
    for (let i = 0; i < forms.length; i++) {
        if(forms[i].name.match(param[0].slice(0,4))) {

            if(forms[i].name == 'form_price') {
                for (let j = 0; j < forms[i].elements.length; j++) {
                    if (forms[i].elements[j].name == param[0]) {
                        forms[i].elements[j].value = param[1];
                    }
                }
            } else if(forms[i].name == 'form_sort1' || forms[i].name == 'form_sort2') {
                    for (let j = 0; j < forms[i].elements[1].options.length; j++) {
                        let select = forms[i].elements[1].options[j]
                        if(select.value == param[1]) {
                            console.log('ok');
                            select.selected = true;;
                            forms[i].elements[1].insertBefore(select, select.parentNode.firstChild);
                        }
                    }
            } else {
                for (let j = 0; j < forms[i].elements.length; j++) {
                    console.log('ok2');
                    if (forms[i].elements[j].value == param[1]) {
                        forms[i].elements[j].setAttribute('checked', 'checked');
                    } 
                }
            }      
            
        } else {
            let elementAdd = document.createElement('input');
            elementAdd.setAttribute('type', 'hidden');
            elementAdd.setAttribute('name', param[0]);
            elementAdd.setAttribute('value', param[1]);
            forms[i].prepend(elementAdd);
        }         
    }        
}
