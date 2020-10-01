"use strict";

let submits = document.querySelectorAll('.submit'); // получение всех кнопок отправки формы
let forms = document.querySelectorAll('form');  // получение всех форм


function addFilterItems(selected, id, select_name) {
    let addArrayForms = Array.from(forms);
    addArrayForms.splice(id, 1);
    for (let s = 0; s < addArrayForms.length; s++) {
        let selectAdd = document.createElement('select');
        selectAdd.style.cssText = "display: none";
        selectAdd.className = select_name;
        selectAdd.setAttribute('name', select_name);
        selectAdd.setAttribute('multiple', 'multiple');

        for (let i = 0; i < selected.length; i++) {
            let optionAdd = document.createElement('option');
            optionAdd.value = selected[i];
            optionAdd.setAttribute('selected', 'selected');
            selectAdd.prepend(optionAdd);
        }
        addArrayForms[s].prepend(selectAdd); // надо здесь поменять на insertBefore
    }
}

function removeFilterItems() {
    //
}

function sendAjaxForm(form) {
    $(form).submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: "index.php",
            type: "GET",
            data: form.serialize(),
            success: function (response) {
                //обработка успешной отправки
            },
            error: function (response) {
                //обработка ошибок при отправке
            }
            
        });
    });
}

for (let i = 0; i < submits.length; i++) {  // для каждой кнопки:
    submits[i].addEventListener('click', function () {   // добавляем слушатель
        event.preventDefault();    // который пока что отменяет отправку
        let form = submits[i].form;  // получает форму, кнопка в которой нажата
        console.log(form);
        // sendAjaxForm(form);
        let id = i;  // получает порядковый номер формы в массиве форм
        console.log(id);
        let select = form.elements[0];  // получает select этой формы
        console.log(select);
        let select_name = select.getAttribute('name'); // получает атрибут name селекта
        console.log(select_name);
        let selected = [];
        for (let n = 0; n < select.length; n++) {   // через цикл находит выделенные options
            if (select.options[n].selected == true)
                selected.push(select.options[n].value);   // и записывает их в массив
        }
        if (selected.length > 0 || !selected.includes('all')) { // если есть выбранные элементы и не содержат сброс фильтра,то
            addFilterItems(selected, id, select_name); // добавляет элементы
        } else removeFilterItems(); // иначе удаляет элементы

    }, false);
}


