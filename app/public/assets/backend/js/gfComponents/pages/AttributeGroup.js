import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";

export default class AttributeGroup extends CrudPage {
    constructor(blockConfig) {
        super();
        this.blockConfig = blockConfig;
        this.modalStyleConfig = {
            create: {
                modalWidth: '80%',
                modalHeight: '60%'
            },
            edit: {
                modalWidth: '80%',
                modalHeight: '60%'
            }
        };
        this.addAdditionalListeners();
    }

    addAdditionalListeners() {
        this.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            this.addSearchCategoryListeners();
            this.addSearchAttributeListeners();
            this.removeSearchListener();
            this.removeSearchListenerAttributes();
            this.getCategoryDataFromSelect();
            this.getAttributeDataFromSelect();
            this.addAddAttributeListener();
            this.addExistingAttributeListeners();
        });
    }


    addAddAttributeListener() {
        this.addAttributeButton = document.getElementById('addAttribute');
        this.addAttributeButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.generateAttribute();
        });
    }

    generateAttribute() {
        let attributeSelect = document.getElementById('attributeId');
        let attributeId = attributeSelect.value;
        if(attributeId === '0') {
            return;
        }

        let container = document.createElement('div');
        container.classList.add('coupleContainer');

        let dummySingleContainer = document.createElement('div');
        dummySingleContainer.classList.add('dummySingleContainer');

        let name = document.createElement('span');
        name.classList.add('dummy');
        name.innerText = attributeSelect.options[attributeSelect.selectedIndex].innerText;

        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'attributes[]';
        input.value = attributeId;

        dummySingleContainer.appendChild(input);

        let deleteDummy = document.createElement('div');
        deleteDummy.classList.add('deleteDummy');
        deleteDummy.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                           </svg>`;

        deleteDummy.addEventListener('click', () => {
           deleteDummy.parentElement.parentElement.remove();
        });

        dummySingleContainer.appendChild(name);
        dummySingleContainer.appendChild(deleteDummy);
        container.appendChild(dummySingleContainer);

        document.getElementById('attributeCollection').appendChild(container);
    }

    addExistingAttributeListeners() {
        document.querySelectorAll('.existingAttribute').forEach((elem) => {
           let deleteButton = elem.querySelector('.deleteDummy');
           deleteButton.addEventListener('click', () => {
               deleteButton.parentElement.parentElement.remove();
           });
        });
    }
    // @TODO remove existing attributes from list




    getCategoryDataFromSelect() {
        this.categorySelect = document.getElementById('categoryId');
        this.masterCategories = [];
        Array.from(this.categorySelect.options).forEach((option) => {
            this.masterCategories.push({id: option.value, value: option.innerText});
        });
    }

    getAttributeDataFromSelect() {
        this.attributeSelect = document.getElementById('attributeId');
        this.attributes = [];
        Array.from(this.attributeSelect.options).forEach((option) => {
            this.attributes.push({id: option.value, value: option.innerText});
        });
    }

    addSearchCategoryListeners() {
        let searchButton = document.querySelectorAll('.searchCategory');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('categorySearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateSearch('categorySearchInputContainer', 'categorySearchInput');
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateResults(this.masterCategories, selectElement));
            });
        });
    }

    addSearchAttributeListeners() {
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
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateResults(this.attributes, selectElement));
            });
        });
    }

    generateSearch(containerClass, inputId) {
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
            this.filterResults(searchInput.value, resultsContainer.querySelectorAll('p'));
            clearTimeout(timeout);
            timeout = setTimeout(() => {
            },100)

        });
        container.appendChild(searchInput);
        container.appendChild(resultsContainer);
        return container;
    }


    removeSearchCategory() {
        document.getElementById('categorySearchInput').parentElement.remove();
    }
    removeSearchAttribute() {
        document.getElementById('attributeSearchInput').parentElement.remove();
    }

    generateResults(results, selectElement) {
        let wrapper = document.createElement('div');
        results.forEach((result) => {
            let resultElem = document.createElement('p');
            resultElem.innerText = result.value;
            resultElem.setAttribute('id', result.id);
            resultElem.addEventListener('click', () => {
                selectElement.value = result.id.toString();
                selectElement.dispatchEvent(new Event('change'));
                if(selectElement.id === 'categoryId') {
                    this.removeSearchCategory();
                }
                if(selectElement.id === 'attributeId') {
                    this.removeSearchAttribute();
                }
            });
            wrapper.appendChild(resultElem);
        });
        return wrapper;
    }

    filterResults(value, targets) {
        targets.forEach((target) => {
            if(this.transliterate(target.innerText).toLowerCase().includes(value.toLowerCase())) {
                target.style.display = 'block';
            } else {
                target.style.display = 'none';
            }
        });
    }

    transliterate(string) {
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

    removeSearchListener() {
        document.body.addEventListener('mousedown', (e) => {
            let inputSearchContainer = document.getElementById('categorySearchInput')?.parentElement.parentElement;
            if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
                this.removeSearchCategory();
            }
        });
    }

    removeSearchListenerAttributes() {
        document.body.addEventListener('mousedown', (e) => {
            let inputSearchContainer = document.getElementById('attributeSearchInput')?.parentElement.parentElement;
            if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
                this.removeSearchAttribute();
            }
        });
    }
}