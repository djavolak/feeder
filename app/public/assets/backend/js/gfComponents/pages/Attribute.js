import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";
import GroupInput from "../components/GroupInput.js";

export default class Attribute extends CrudPage {
    constructor(blockConfig) {
        super();
        this.blockConfig = blockConfig;
        this.modalStyleConfig = {
            create: {
                modalWidth: '80%',
                modalHeight: '80%'
            },
            edit: {
                modalWidth: '80%',
                modalHeight: '80%'
            }
        };
        this.addAdditionalListeners();
    }

    addAdditionalListeners() {
        this.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            this.intGroupInitForAttributeValues();
            this.getAttributeGroupDataFromSelect();
            this.initAttributeGroupHandles();
            this.removeSearchListener();
            this.addGroupListeners();
            this.addExistingAttributeListeners();
        });
    }

    addGroupListeners() {
        this.addGroupButton = document.getElementById('addGroup');
        this.addGroupButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.generateGroup();
            document.getElementById('attributeGroupId').value = '0';
        });
    }

    generateGroup() {
        let groupSelect = document.getElementById('attributeGroupId');
        let attributeGroupId = groupSelect.value;
        if(attributeGroupId === '0') {
            return;
        }

        let container = document.createElement('div');
        container.classList.add('coupleContainer');

        let dummySingleContainer = document.createElement('div');
        dummySingleContainer.classList.add('dummySingleContainer');

        let name = document.createElement('span');
        name.classList.add('dummy');
        name.innerText = groupSelect.options[groupSelect.selectedIndex].innerText;

        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'attributeGroup[]';
        input.value = attributeGroupId;

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

        document.getElementById('groupCollection').appendChild(container);
    }

    addExistingAttributeListeners() {
        document.querySelectorAll('.existingGroup').forEach((elem) => {
            let deleteButton = elem.querySelector('.deleteDummy');
            deleteButton.addEventListener('click', () => {
                deleteButton.parentElement.parentElement.remove();
            });
        });
    }
    // @TODO remove existing groups from list

    intGroupInitForAttributeValues() {
        let groupInput = new GroupInput({
            containerId: 'groupInputContainer',
            inputId: 'attributeInput',
            inputValueName: 'attributeValues',
            dummyContainerId: 'dummyContainer',
            form: this.modal.getFormInModal()
        });
        groupInput.init();
    }




    getAttributeGroupDataFromSelect() {
        this.attributeGroupSelect = document.getElementById('attributeGroupId');
        this.attributeGroups = [];
        Array.from(this.attributeGroupSelect.options).forEach((option) => {
            this.attributeGroups.push({id: option.value, value: option.innerText});
        });
    }



    initAttributeGroupHandles() {
            this.addSearchGroupListener();
    }

    addSearchGroupListener() {
        let searchButton = document.querySelectorAll('.searchGroup');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('groupSearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateSearch('groupSearchInputContainer', 'groupSearchInput');
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateResults(this.attributeGroups, selectElement));
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

    removeSearchGroups() {
        document.getElementById('groupSearchInput').parentElement.remove();
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
                this.removeSearchGroups();
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
            let inputSearchContainer = document.getElementById('groupSearchInput')?.parentElement.parentElement;
            if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
                this.removeSearchGroups();
            }
        });
    }

}