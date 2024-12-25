document.addEventListener('DOMContentLoaded', () => {
    generateSearchForCategories();
    removeSearchListener();
    removeSearchListenerAttributes();

    addCategorySelectedListener();
    addSearchAttributeListeners();
    addAttributeListener();
    addSortable();
});


function addSortable() {
    $( function() {
        $("#attributeListContainer").sortable();
    } );
}

function addAttributeListener() {
    let addAttributeButton = document.getElementById('addAttribute');
    addAttributeButton.addEventListener('click', () => {
       if(!document.getElementById('submitForm')) {
           return;
       }
       generateAttribute(document.getElementById('attributeId').value);
    });
}

function addCategorySelectedListener() {
    let select = document.getElementById('categoryId');
    select.addEventListener('change', async () => {
        let res = await getAttributesForCategory(select.value);
        let attributeIds = res.attributeIds;
        clearContainer();
        generateAttributes(attributeIds);
        generateSubmitButton();
    });
}

function generateSubmitButton() {
    let submit = document.createElement('button');
    submit.id = 'submitForm';
    submit.classList.add('btn','btn-success');
    submit.innerText = 'Save';
    submit.style.width = 'max-content';
    submit.style.order = '1';
    submit.addEventListener('click', async () => {
        let attributeIdsForSubmissionInputs = document.querySelectorAll('input[name="attributeIds[]"]');
        if(attributeIdsForSubmissionInputs.length > 0) {
            let attributeIds = [];
            attributeIdsForSubmissionInputs.forEach((attributeId) => {
                attributeIds.push(attributeId.value);
            });
            let res = await submitAttributes(attributeIds);
            printMessage(res.message);
            setTimeout(() => {
                removeMessage();
            },2000);
            clearContainer();
            document.getElementById('categoryId').value = '0';
        }
    });
    document.getElementById('attributeListContainer').appendChild(submit);
}

async function submitAttributes(attributeIds) {
    let action = '/attribute/saveAttributesForCategory/';
    let formData = new FormData();
    formData.append('attributeIds', attributeIds);
    formData.append('categoryId', document.getElementById('categoryId').value);
    let req = await fetch(action, {
        method: 'POST',
        body: formData
    });
    return  JSON.parse(await req.text());
}

function addSearchAttributeListeners() {
    let searchButton = document.querySelectorAll('.searchAttributes');
    searchButton.forEach((button) => {
        button.addEventListener('click', () => {
            if(document.getElementById('attributeSearchInput')) {
                return;
            }
            let parent = button.parentElement;
            let selectElement = parent.parentElement.querySelector('select');
            let search = this.generateSearch('attributeSearchInputContainer', 'attributeSearchInput');
            parent.appendChild(search);
            search.querySelector('input').focus();
            search.querySelector('.scrollableContainerCustomHeight').appendChild(generateResults(getAttributeDataFromSelect(), selectElement));
        });
    });
}

function clearContainer() {
    document.getElementById('attributeListContainer').innerHTML = '';
}

function generateAttributes(attributeIds) {
    attributeIds.forEach((attributeId) => {
        generateAttribute(attributeId.attributeId);
    });
}

function printMessage(message) {
    let container =  document.getElementById('messageContainer');
    container.classList.add('alert', 'alert-success'); //@todo validate through response
    container.innerText = message;

}

function removeMessage() {
    let container =  document.getElementById('messageContainer');
    container.classList.remove('alert', 'alert-success');
    document.getElementById('messageContainer').innerText = '';
}

function getAttributeNameFromSelectById(id) {
   let option = document.querySelector(`#attributeId option[value="${id}"]`);
   if(option.value !== '0') {
       return option.innerText;
   }
   return null;
}

function generateAttribute(attributeId) {
    document.getElementById('attributeId').value = '0';
    if(document.querySelector(`div[data-attribute-id="${attributeId}"]`)) {
        alert('Attribute already selected');
        return;
    }
    let attributeName = getAttributeNameFromSelectById(attributeId);
    if(attributeName) {
        let attr = document.createElement('div');
        attr.classList.add('btn', 'btn-primary');
        attr.innerText = attributeName;
        attr.setAttribute('data-attribute-id', attributeId);
        attr.style.position = 'relative';

        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'attributeIds[]';
        input.value = attributeId;
        attr.appendChild(input);

        let deleteElem = document.createElement('div');
        deleteElem.addEventListener('click', () => {
           deleteElem.parentElement.remove();
        });
        deleteElem.classList.add('secondDescriptionAttributeDelete');
        deleteElem.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                           </svg>`;
        attr.appendChild(deleteElem);
        document.getElementById('attributeListContainer').appendChild(attr);
    }
}

async function getAttributesForCategory(categoryId) {
    let action = '/attribute/getAttributesForCategory/';
    let formData = new FormData();
    formData.append('categoryId', categoryId);
    let req = await fetch(action, {
        method: 'POST',
        body: formData
    });
    return  JSON.parse(await req.text());
}


function generateSearchForCategories() {
    let masterCategories = getCategoryDataFromSelect();
    let searchButton = document.querySelectorAll('.searchCategory');
    searchButton.forEach((button) => {
        button.addEventListener('click', () => {
            if(document.getElementById('categorySearchInput')) {
                return;
            }
            let parent = button.parentElement;
            let selectElement = parent.parentElement.querySelector('select');
            let search = generateSearch('categorySearchInputContainer', 'categorySearchInput');
            parent.appendChild(search);
            search.querySelector('input').focus();
            search.querySelector('.scrollableContainerCustomHeight').appendChild(generateResults(masterCategories, selectElement));
        });
    });
}

function getAttributeDataFromSelect() {
    let attributeSelect = document.getElementById('attributeId');
    let attributes = [];
    Array.from(attributeSelect.options).forEach((option) => {
        attributes.push({id: option.value, value: option.innerText});
    });
    return attributes;
}

function getCategoryDataFromSelect() {
    let categorySelect = document.getElementById('categoryId');
    let masterCategories = [];
    Array.from(categorySelect.options).forEach((option) => {
        masterCategories.push({id: option.value, value: option.innerText});
    });
    return masterCategories;
}

function generateResults(results, selectElement) {
    let wrapper = document.createElement('div');
    results.forEach((result) => {
        let resultElem = document.createElement('p');
        resultElem.innerText = result.value;
        resultElem.setAttribute('id', result.id);
        resultElem.addEventListener('click', () => {
            selectElement.value = result.id.toString();
            selectElement.dispatchEvent(new Event('change'));
            if(selectElement.id === 'categoryId') {
                removeSearchCategory();
            }
            if(selectElement.id === 'attributeId') {
                removeSearchAttribute();
            }
        });
        wrapper.appendChild(resultElem);
    });
    return wrapper;
}

function removeSearchCategory() {
    document.getElementById('categorySearchInput').parentElement.remove();
}
function removeSearchAttribute() {
    document.getElementById('attributeSearchInput').parentElement.remove();
}

function generateSearch(containerClass, inputId) {
    let container = document.createElement('div');
    container.classList.add(containerClass)
    let searchInput = document.createElement('input');
    searchInput.classList.add('form-control');
    searchInput.id = inputId;

    let resultsContainer = document.createElement('div');
    resultsContainer.classList.add('scrollableContainerCustomHeight');
    resultsContainer.style.width = '200%';

    let timeout = null;
    searchInput.addEventListener('input', () => {
        filterResults(searchInput.value, resultsContainer.querySelectorAll('p'));
        clearTimeout(timeout);
        timeout = setTimeout(() => {
        },100)

    });
    container.appendChild(searchInput);
    container.appendChild(resultsContainer);
    return container;
}

function filterResults(value, targets) {
    targets.forEach((target) => {
        if(transliterate(target.innerText).toLowerCase().includes(value.toLowerCase())) {
            target.style.display = 'block';
        } else {
            target.style.display = 'none';
        }
    });
}

function transliterate(string) {
    return string
        .replace(/ž/g, 'z')
        .replace(/š/g, 's')
        .replace(/đ/g, 'dj')
        .replace(/č/g, 'c')
        .replace(/ć/g, 'c')
        .replace(/Ž/g, 'z')
        .replace(/Š/g, 's')
        .replace(/Đ/g, 'dj')
        .replace(/Č/g, 'c')
        .replace(/Ć/g, 'c');
}

function removeSearchListener() {
    document.body.addEventListener('mousedown', (e) => {
        let inputSearchContainer = document.getElementById('categorySearchInput')?.parentElement.parentElement;
        if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
            this.removeSearchCategory();
        }
    });
}

function removeSearchListenerAttributes() {
    document.body.addEventListener('mousedown', (e) => {
        let inputSearchContainer = document.getElementById('attributeSearchInput')?.parentElement.parentElement;
        if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
            this.removeSearchAttribute();
        }
    });
}