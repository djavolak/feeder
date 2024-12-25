import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {mediaLibraryEvents} from "../mediaLibrary/mediaLibraryEvents.js";
import MediaUploader from "../components/ImageUploader/MediaUploader.js";
import {ImagePreviewInForm} from "../imagePreviewInForm/ImagePreviewInForm.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/pageEvents.js";
import ValuesGenerator from "../valuesGenerator/ValuesGenerator.js";
import PostEditorWrapper from "https://skeletor.greenfriends.systems/skeletor-assets/0.1/postEditor/PostEditorWrapper.js";

export default class Product extends CrudPage {
    postEditorContainer;
    postEditor;

    constructor(blockConfig) {
        super();
        this.blockConfig = blockConfig;
        this.dataTableOptions = {
            enableCheckboxes: true,
            shiftCheckboxModifier: true
        };
        this.modalOptions = {
            createModalWidth: '100%',
            createModalHeight: '100%',
            editModalWidth: '100%',
            editModalHeight: '100%'
        }
        this.postEditorWrapper = new PostEditorWrapper();
        this.addAdditionalListeners();
        this.addFormReadyListeners();
        this.masterCategories = null;
        this.attributes = null;
        ImagePreviewInForm.handleFormWithImage(this);


        //
    }

    addFormReadyListeners() {
        this.eventEmitter.on(pageEvents.entityFormReady, (data) => {
            this.initPostEditor(data);

            // if(document.getElementById('bulkEditContainer')) {
            //     this.addAdditionalBulkListeners();
            //     return;
            // }
            this.loadValueGenerators();
            // this.addSalePriceListeners();
            this.setProps();
            // this.addCategoryControl();
            this.addTabControl();
            // this.addAttributeListeners();
            // this.addFeaturedImageUpload();
            // this.addGalleryImagesUpload();
            this.scrollToSelectedCategory();
            // MediaUploader.registerExistingPreviews();
            // this.addAttributesGroupListener();
            this.addTagHandle();
            // this.addLoopSalePriceListener();

            //
            document.getElementById('submitButton').disabled = false;
        });
    }

    addAdditionalListeners() {
        this.setRuntimeFilter();
        this.addMediaInsertedListener();
        // this.addAdditionalDataChangedListeners();
        // this.generateSearchForCategoryFilter();
        // this.removeSearchListener();
        // this.getAttributes().then(() => {
        //     this.includeExcludeAttributeFilter();
        // });

        // this.crudTable.crudTable.addEventListener(this.crudTable.tablePopulatedEventName, () => {
        //     this.addCategoryLinkListeners();
        // });
    }

    generateSelectAttributes() {
        let container = document.createElement('div');
        let deleteAttribute = document.createElement('div');
        container.classList.add('selectAttributeContainer');
        let searchWrapper = document.createElement('div');
        searchWrapper.classList.add('searchContent');
        searchWrapper.setAttribute('data-action', '/attribute/tableHandler/');
        searchWrapper.style.cursor = 'pointer';
        searchWrapper.innerHTML = `<svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                    </svg>`
        let ajaxSearch = new SimpleAjaxSearch({
            initiator: searchWrapper,
            placeholder: 'Search attributes',
            dispatchTank: container,
            resultClickedEventName: 'attributeSelected',
            limit: 20,
            charactersSearchThreshold: 2,
            delayOnSearchInMs: 300,
            resultViewParameter: 'attributeName',
            noMoreResultsMessage: 'No more attributes',
        });
        let selectAttribute = document.createElement('select');
        selectAttribute.classList.add('form-control');
        let selectAttributeValue = document.createElement('select');
        selectAttributeValue.classList.add('form-control');

        let selectAttributeOptionBase = document.createElement('option');
        selectAttributeOptionBase.innerText = 'Choose an attribute';
        selectAttributeOptionBase.value = '-1';
        selectAttribute.appendChild(selectAttributeOptionBase);
        this.attributes.forEach((attribute, index) => {
            let selectAttributeOption = document.createElement('option');
            selectAttributeOption.innerText = attribute.attributeName;
            selectAttributeOption.value = attribute.attributeId;
            selectAttributeOption.setAttribute('data-index', index);
            selectAttribute.appendChild(selectAttributeOption);
        });
        let decodeHtmlEntity = function(str) {
            return str.replace(/&#(\d+);/g, function(match, dec) {
                return String.fromCharCode(dec);
            });
        };
        selectAttribute.addEventListener('change', () => {
            selectAttributeValue.innerHTML = '';
            let chooseOption = document.createElement('option');
            chooseOption.value = '';
            chooseOption.innerText = 'Choose Value';
            selectAttributeValue.appendChild(chooseOption);
           let index = selectAttribute.selectedOptions[0].getAttribute('data-index');
           this.attributes[index].attributeValues.forEach((attribute) => {
               let selectAttributeValueOption = document.createElement('option');
               selectAttributeValueOption.innerText = decodeHtmlEntity(attribute.attributeValue);
               selectAttributeValueOption.value = attribute.attributeValueId;
               selectAttributeValue.appendChild(selectAttributeValueOption);
           });
            let searchWrapperAttrValues = document.createElement('div');
            searchWrapperAttrValues.classList.add('searchContentAttrValues');
            searchWrapperAttrValues.setAttribute('data-action', '/attribute-values/tableHandler/');
            searchWrapperAttrValues.style.cursor = 'pointer';
            searchWrapperAttrValues.innerHTML = `<svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                    </svg>`
           let attributeValuesSearch = new SimpleAjaxSearch({
               initiator: searchWrapperAttrValues,
               placeholder: 'Search attribute values',
               dispatchTank: container,
               resultClickedEventName: 'attributeValueSelected',
               limit: 20,
               charactersSearchThreshold: 2,
               delayOnSearchInMs: 300,
               resultViewParameter: 'attributeValue',
               noMoreResultsMessage: 'No more attribute values',
           });
            if (container.querySelector('.searchContentAttrValues')) {
                container.querySelector('.searchContentAttrValues').remove();
            }
            deleteAttribute.parentNode.insertBefore(searchWrapperAttrValues, deleteAttribute);
            attributeValuesSearch.setCustomPostData('filter', JSON.stringify({'attributeId': selectAttribute.value}));
            attributeValuesSearch.mount();
            container.addEventListener('attributeValueSelected', (e) => {
                selectAttributeValue.value = e.detail.id;
            });
        });
        deleteAttribute.classList.add('includeExcludeDelete');
        deleteAttribute.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>`;
        deleteAttribute.addEventListener('click', () => {
           container.remove();
        });
        container.appendChild(selectAttribute);
        container.appendChild(searchWrapper);
        container.appendChild(selectAttributeValue);
        container.appendChild(deleteAttribute);
        ajaxSearch.mount();
        container.addEventListener('attributeSelected', (e) => {
            selectAttribute.value = e.detail.id;
            selectAttribute.dispatchEvent(new Event('change'));
        });
        return container;
    }

    includeExcludeAttributeFilter() {
        document.querySelector('.panel-body').insertBefore(this.generateIncludeExcludeButton(), document.querySelector('.table.tableControls'));
    }
    initPostEditor(data) {
        this.descriptionEditor = this.postEditorWrapper.initEditor('#descriptionContainer', [], this.blockConfig);
        this.shortDescriptionEditor = this.postEditorWrapper.initEditor('#shortDescriptionContainer', [], this.blockConfig);

        if(data && data.action && data.action === 'edit') {
            let blockData = this.getBlockData('.descriptionBlockData');
            if(blockData) {
                this.descriptionEditor.preloadBlocks(blockData);
            }
            blockData = this.getBlockData('.shortDescriptionBlockData');
            if(blockData) {
                this.shortDescriptionEditor.preloadBlocks(blockData);
            }
        }
    }

    getBlockData(containerSelector) {
        const elem = document.querySelector(containerSelector);
        if(elem && elem.getAttribute('data-blocks')) {
            return JSON.parse(elem.getAttribute('data-blocks'));
        }
        return null;
    }

    loadValueGenerators() {
        const form = this.modal.getFormInModal();
        if(form) {
            let valuesGeneratorContainers = ValuesGenerator.getAllContainersForInit(form);
            if(valuesGeneratorContainers) {
                valuesGeneratorContainers.forEach((container) => {
                    const valuesGenerator = new ValuesGenerator(container, form);
                    valuesGenerator.init();
                });
            }
        }
    }

    addMediaInsertedListener() {
        const mediaLibraryEmitter = window.mediaLibrary.eventEmitter;
        mediaLibraryEmitter.on(mediaLibraryEvents.mediaReadyToInsert, (data) => {
            if (data.initiatorElement && this.postEditor && this.postEditor.getBlocksContainer().contains(data.initiatorElement)) {
                // Image block
                const blockId = data.initiatorElement.blockId;
                if(blockId || blockId === 0) {
                    const block = this.postEditor.getActiveBlockById(blockId);
                    if(typeof block.handleImageSelection === 'function') {
                        block.handleImageSelection(data.initiatorElement, data.media);
                    }
                }
            }
        });
    }


    addTagHandle() {
        this.tagSelect.addEventListener('change', () => {
           let selectedOption = this.disableSelectedTagOption();
           this.tagSelect.value = '-1';
           this.tagsContainer.appendChild(this.generateTagEntity(selectedOption.innerText, selectedOption.value));
        });

        this.handleExistingTags();
    }

    handleExistingTags() {
        let existingTags = document.querySelectorAll('.tagContainer');
        existingTags.forEach((tag) => {
            let value = tag.getAttribute('data-tag-id');
           this.disableSelectedTagOption(value);
            tag.querySelector('.deleteTag').addEventListener('click', () => {
                this.enableTagOption(value);
                tag.remove();
            });
        });
    }

    disableSelectedTagOption(value = null) {
        let selectedOption;
        if(value) {
            selectedOption = this.tagSelect.querySelector(`option[value="${value}"]`);
        } else {
            selectedOption = this.tagSelect.querySelector(`option[value="${this.tagSelect.value}"]`);
        }
        selectedOption.disabled = true;
        selectedOption.style.background = 'lightgray';
        selectedOption.style.color = 'white';
        return selectedOption;
    }

    enableTagOption(value) {
        let option = this.tagSelect.querySelector(`option[value="${value}"]`);
        option.disabled = false;
        option.style.background = 'white';
        option.style.color = '#6e707e';
    }

    generateTagEntity(name, value) {
        let tagContainer = document.createElement('div');
        tagContainer.classList.add('tagContainer');
        let input = document.createElement('input');
        //check if we deleted an existing tag previously
        let deletedTagInput = document.querySelector(`.deletedTag[value="${value}"]`);
        if(deletedTagInput) {
            input.name = `existingTags[${value}]`;
        } else {
            input.name = `tags[${value}]`;
        }
        input.value = name;
        input.type = 'hidden';
        tagContainer.appendChild(input);
        let text = document.createElement('span');
        text.innerText = name;
        tagContainer.appendChild(text);

        let deleteElement = document.createElement('div');
        deleteElement.classList.add('deleteTag');
        deleteElement.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                      </svg>`;
        deleteElement.addEventListener('click', () => {
            if(deletedTagInput) {
                this.appendDeletedTagInput(value);
            }
           tagContainer.remove();
           this.enableTagOption(value);
        });
        if(deletedTagInput) {
            deletedTagInput.remove();
        }
        tagContainer.appendChild(deleteElement);
        return tagContainer;
    }

    generateIncludeExcludeButton() {
        let container = document.createElement('div');
        container.id = 'includeExcludeButtonContainer';
        let button = document.createElement('button');
        let clear = document.createElement('button');
        button.id = 'includeExcludeButton';
        button.classList.add('btn','btn-primary');
        button.innerText = 'Include/Exclude Attributes';

        clear.id = 'resetIncludeExclude';
        clear.classList.add('btn','btn-danger');
        clear.innerText = 'Rest Include/Exclude Attributes';
        container.appendChild(button);
        container.appendChild(clear);
        this.addIncludeExcludeListener(button, clear);
        return container;
    }

    addIncludeExcludeListener(button, clear) {
        button.addEventListener('click', () => {
            this.openIncludeExcludeModal();
        });
        clear.addEventListener('click', () => {
           if(this.crudTable.filterData.includeAttributes || this.crudTable.filterData.excludeAttributes) {
              this.crudTable.filterData = {};
               this.crudTable.setupTable(0, null);
           }
        });
    }

    openIncludeExcludeModal() {
        this.modal.openModal('600px','600px');
        let content = `<h2>Include Attributes</h2>
                            <div id="includeAttributesContainer">
                                <div id="addIncludeAttributes">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>`;
        content += `<h2>Exclude Attributes</h2>
                            <div id="excludeAttributesContainer">
                                <div id="addExcludeAttributes">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>`;

        content += `<button id="includeExcludeApply" class="btn btn-primary">Apply</button>`;
        this.modal.populateModal(content);
        this.populateIncludeExcludeIfOldDataExists();
        this.addAttributesListenerInModal();
    }

    populateIncludeExcludeIfOldDataExists() {
        let includeContainer = document.getElementById('includeAttributesContainer');
        let excludeContainer = document.getElementById('excludeAttributesContainer');
        if(this.crudTable.filterData.includeAttributes) {
            this.crudTable.filterData.includeAttributes.forEach((includeAttributes) => {
               let container = this.generateSelectAttributes();
               container.querySelectorAll('select')[0].value = includeAttributes.attributeId;
                container.querySelectorAll('select')[0].dispatchEvent(new Event('change'));
               container.querySelectorAll('select')[1].value = includeAttributes.attributeValueId;
                includeContainer.appendChild(container);
            });
        }
        if(this.crudTable.filterData.excludeAttributes) {
            this.crudTable.filterData.excludeAttributes.forEach((excludeAttributes) => {
                let container = this.generateSelectAttributes();
                container.querySelectorAll('select')[0].value = excludeAttributes.attributeId;
                container.querySelectorAll('select')[0].dispatchEvent(new Event('change'));
                container.querySelectorAll('select')[1].value = excludeAttributes.attributeValueId;
                excludeContainer.appendChild(container);
            });
        }
    }

    addAttributesListenerInModal() {
        let addIncludeButton = document.getElementById('addIncludeAttributes');
        let addExcludeButton = document.getElementById('addExcludeAttributes');
        let applyButton = document.getElementById('includeExcludeApply');
        addIncludeButton.addEventListener('click', () => {
            addIncludeButton.parentElement.appendChild(this.generateSelectAttributes());
        });

        addExcludeButton.addEventListener('click', () => {
            addExcludeButton.parentElement.appendChild(this.generateSelectAttributes());
        });

        applyButton.addEventListener('click', () => {
            this.crudTable.filterData = {}
            this.crudTable.filterData.includeAttributes = this.getIncludeAttributes();
            this.crudTable.filterData.excludeAttributes = this.getExcludeAttributes();
            this.modal.closeModal();
            this.crudTable.setupTable(0, null);
        });
    }
    getIncludeAttributes() {
        let data = [];
        let container = document.getElementById('includeAttributesContainer');
        container.querySelectorAll('.selectAttributeContainer').forEach((innerContainer) => {
           let attributeId = parseInt(innerContainer.querySelectorAll('select')[0].value);
           let attributeValueId = parseInt(innerContainer.querySelectorAll('select')[1].value);
           data.push({'attributeId': attributeId, 'attributeValueId': attributeValueId});
        });
        return data;
    }

    getExcludeAttributes() {
        let data = [];
        let container = document.getElementById('excludeAttributesContainer');
        container.querySelectorAll('.selectAttributeContainer').forEach((innerContainer) => {
            let attributeId = parseInt(innerContainer.querySelectorAll('select')[0].value);
            let attributeValueId = parseInt(innerContainer.querySelectorAll('select')[1].value);
            data.push({'attributeId': attributeId, 'attributeValueId': attributeValueId});
        });
        return data;
    }


    addAdditionalDataChangedListeners() {
        document.body.addEventListener(this.dataChangedEventName, (e) => {
            let data = JSON.parse(e.detail.data)
            if(data.productId) {
                this.openModalIfIdInGet(data.productId);
            }
        });
    }

    addCategoryLinkListeners() {
        let catLinks = document.querySelectorAll('.categoryLink');
        catLinks.forEach((catLink) => {
           catLink.addEventListener('click', (e) => {
               e.preventDefault();
               const url = new URL(window.location.href);
               url.searchParams.set('category', catLink.getAttribute('data-cat-id'));
               let newUrl = url.href.replace(/\+/g,"%20"); // replace + with encode
               window.history.pushState(null, null, newUrl);
               this.crudTable.setupTable(0, null);
           });
        });
    }

    addAdditionalBulkListeners() {
       document.querySelectorAll('.deleteProductFromEdit').forEach((deleteButton) => {
           deleteButton.addEventListener('click', () => {
              deleteButton.parentElement.remove();
           });
        })
        this.getMasterCategories().then((masterCategories) => {
            this.addSearchListeners([{value:'No Change', id:-1},...masterCategories]);
        });

       // this.addBulkAttributeListeners();
       // this.addBulkDeleteAttributeListener();
       // this.addBulkTagListeners();
       // this.addBulkSalePriceListeners();
    }

    addBulkAttributeListeners() {
        let attributeSearchInput = new InputAjaxSearch({
            containerId: 'attributeSearchContainer',
            searchInputId: 'entitySearchInput',
            optionClickCallback: this.bulkAttributeSelectedCallback,
            fetchUrl: `/product/getAttributes/?query=`,
            showOptionsBeforeInput: false,
            inputClass: 'form-control',
            placeholder: 'Search...',
            packageName: 'attribute',
            searchAllOnIconClick: true
        });
        attributeSearchInput.init();
        this.getAttributeGroupDataFromSelect();
        this.addSearchGroupListener();
        this.addGroupListener();
    }

    addBulkTagListeners() {
        this.addBulkDeleteTagListener();
        this.getTagDataFromSelect();
        this.addBulkTagSearchListener();
        this.addTagListener();
    }

    addBulkTagSearchListener() {
        let searchButton = document.querySelectorAll('.searchTags');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('tagsSearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateSearch('tagsSearchInputContainer', 'tagsSearchInput');
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateResults(this.tags, selectElement));
            });
        });
    }

    addBulkDeleteTagListener() {
        let tagsContainer = document.getElementById('bulkTagsToDelete');
        document.querySelectorAll('.deleteTagBulk').forEach((deleteTag) => {
            deleteTag.addEventListener('click', () => {
                tagsContainer.appendChild(this.generateTagDeletedBulkInput(deleteTag.getAttribute('data-tag-id')));
                deleteTag.parentElement.remove();
            });
        });
    }

    generateTagDeletedBulkInput(tagId) {
        let input = document.createElement('input');
        input.name = 'deletedTagBulk[]';
        input.type = 'hidden';
        input.value = tagId;
        return input;
    }

    addTagListener() {
        this.addTagButton = document.getElementById('addTag');
        this.tagsToBeAddedContainer = document.getElementById('tagsToBeAddedContainer');
        this.addTagButton.addEventListener('click', (e) => {
            e.preventDefault();
            if(this.tagsSelectBulk.value === '-1') {
                this.tagsSelectBulk.value = '-1';
                return;
            }
            let selectedOption = this.tagsSelectBulk.options[ this.tagsSelectBulk.selectedIndex];
            if(document.querySelector(`.tagToBeAddedEntity[data-id="${selectedOption.value}"]`)) {
                alert(`${selectedOption.innerText} already exists in the list of tags to be added`);
                this.tagsSelectBulk.value = '-1';
                return;
            }
            this.tagsToBeAddedContainer.appendChild(this.generateTagToBeAdded(selectedOption.value, selectedOption.innerText));
            this.tagsSelectBulk.value = '-1';
        })
    }

    generateTagToBeAdded(value, name) {
        let templateContent = document.getElementById('tagToBeAddedTemplate').content.cloneNode(true);
        let tagName = templateContent.querySelector('span');
        tagName.innerText = name;
        templateContent.querySelector('.tagToBeAddedEntity').setAttribute('data-id', value);

        let input = templateContent.querySelector('input');
        input.value = name;
        input.name = `tagsToBeAdded[${value}]`;
        let deleteButton = templateContent.querySelector('.deleteTagToBeAdded');
        deleteButton.addEventListener('click', () => {
            deleteButton.parentElement.remove();
        });
        return templateContent;
    }

    addGroupListener() {
        this.addGroupButton = document.getElementById('addGroup');
        this.addGroupButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.generateGroup(document.getElementById('attributeGroupId').value).then(() => {
                document.getElementById('attributeGroupId').value = '0';
            });
        });
    }

    async generateGroup(groupId) {
        let action = '/attribute-group/getAttributeWithValuesByGroup/';
        let data = new FormData();
        data.append('groupId', groupId)
        let req = await fetch(action, {
            method: 'POST',
            body: data
        });
        let res = JSON.parse(await req.text());
        let attributeData = res.attributes;
        attributeData.forEach((attribute) => {
            let dummyData = {
                id: attribute.attributeId,
                attributeValues: attribute.values,
                attributeName: attribute.attributeName
            }
            this.bulkAttributeSelectedCallback(dummyData);
        });
    }

    getAttributeGroupDataFromSelect() {
        this.attributeGroupSelect = document.getElementById('attributeGroupId');
        this.attributeGroups = [];
        Array.from(this.attributeGroupSelect.options).forEach((option) => {
            this.attributeGroups.push({id: option.value, value: option.innerText});
        });
    }

    getTagDataFromSelect() {
        this.tagsSelectBulk = document.getElementById('tagSelectBulk');
        this.tags = [];
        Array.from(this.tagsSelectBulk.options).forEach((option) => {
            if(option.value === '-1') {
                return;
            }
            this.tags.push({id: option.value, value: option.innerText});
        });
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

    addBulkDeleteAttributeListener() {
        let deleteButtons = document.querySelectorAll('.deleteAttributeBulk');
        deleteButtons.forEach((deleteButton) => {
           deleteButton.addEventListener('click', () => {
              deleteButton.parentElement.remove();
           });
        });
    }

    bulkAttributeSelectedCallback(data) {
        let attributeWrapper = document.createElement('div');
        attributeWrapper.classList.add('productAttributeWrapper');
        attributeWrapper.style.marginBottom = '1rem';

        let attributeName = document.createElement('input');
        attributeName.value = data.attributeName;
        attributeName.name = `attribute[${data.id}][][name]`;
        attributeName.readOnly = true;
        attributeName.classList.add('attributeName', 'form-control');

        attributeWrapper.appendChild(attributeName);

        let attributeValue = document.createElement('select');
        attributeValue.setAttribute('data-attribute-id', data.id);
        attributeValue.classList.add('form-control');
        attributeValue.name = `attribute[${data.id}][][value]`;
        let initialOption = document.createElement('option');
        initialOption.innerText = 'Choose a Value';
        initialOption.value = '-1';
        attributeValue.appendChild(initialOption);
        if(data.attributeValues.length > 0) {
            data.attributeValues.forEach((option) => {
                let optionElem = document.createElement('option');
                optionElem.innerText = option.attributeValue;
                optionElem.value = `${option.attributeValue}#${option.attributeValueId}`;
                attributeValue.appendChild(optionElem);
            });
        }
        attributeWrapper.appendChild(attributeValue);

        let deleteAttribute = document.createElement('div');
        deleteAttribute.addEventListener('click', () => {
            deleteAttribute.parentElement.remove();
        });
        deleteAttribute.classList.add('deleteProductAttribute');
        deleteAttribute.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                      </svg>`;
        attributeWrapper.appendChild(deleteAttribute);

        document.getElementById('attributes').appendChild(attributeWrapper);
    }

    async getMasterCategories() {
        if(this.masterCategories) {
            return this.masterCategories;
        }
        let action = '/category/getMasterCats/';
        let req = await fetch(action, {
            method: 'POST',
        });
        this.masterCategories =  JSON.parse(await req.text());
        return this.masterCategories;
    }

    addSearchListeners(masterCategories) {
        let searchButton = document.querySelectorAll('.searchCategory');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('categorySearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateSearch();
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateResults(masterCategories, selectElement));
            });
        });
    }
    generateSearch() {
        let container = document.createElement('div');
        container.classList.add('categorySearchInputContainer')
        let searchInput = document.createElement('input');
        searchInput.classList.add('form-control');
        searchInput.id = 'categorySearchInput';

        let resultsContainer = document.createElement('div');
        resultsContainer.classList.add('scrollableContainerCustomHeight');

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

    removeSearch() {
        document.getElementById('categorySearchInput').parentElement.remove();
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
                this.removeSearch();
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
                this.removeSearch();
            }
        });
    }

    getSearchParameters() {
        var prmstr = window.location.search.substr(1);
        return prmstr != null && prmstr != "" ? this.transformToAssocArray(prmstr) : {};
    }

    transformToAssocArray( prmstr ) {
        var params = {};
        var prmarr = prmstr.split("&");
        for ( var i = 0; i < prmarr.length; i++) {
            var tmparr = prmarr[i].split("=");
            params[tmparr[0]] = tmparr[1];
        }
        return params;
    }

    setRuntimeFilter() {
        if (typeof this.getSearchParameters().categoryId !== 'undefined') {
            document.getElementsByName('categoryFilter')[0].value = this.getSearchParameters().categoryId.toString();
        }
        if (typeof this.getSearchParameters().category !== 'undefined') {
            document.getElementsByName('categoryFilter')[0].value = this.getSearchParameters().category.toString();
        }
    }

    setProps() {
        this.form = this.modal.getFormInModal();
        this.categoryContainer = this.form.querySelector('.categoryList .inputContainer');
        this.categorylabels = this.categoryContainer.querySelectorAll('label');
        this.categoryCheckboxElements = this.form.querySelectorAll('.categoryCheckbox');
        this.searchCategoriesInput = this.form.querySelector('#categorySearch');
        this.attributesContainer = this.form.querySelector('#attributes');
        this.createNewAttributeButton = this.form.querySelector('#createNewAttribute');
        this.newAttributeCounter = 0;
        this.productAttributes = {};
        this.productAttributeDeletedEventName = 'productAttributeDeleted';
        this.regenerateSecondDescriptionEventName = 'regenerateSecondDescription';
        this.tagSelect = document.getElementById('tagSelect');
        this.tagsContainer = document.getElementById('tagsContainer');
    }

    addCategoryControl() {
        this.addSearchCategoryListener();
        this.addAutoParentSelect();
    }

    addSearchCategoryListener() {
        let timeout = null;
        this.searchCategoriesInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.showAllCategories();
                if(this.searchCategoriesInput.value.length === 0) {
                    return;
                }
                this.filterCategoryView(this.searchCategoriesInput.value);
            },300);
        });
    }

    showAllCategories() {
        this.categorylabels.forEach((label) => {
            label.classList.remove('hidden');
        });
    }

    filterCategoryView(search) {
        this.categorylabels.forEach((label) => {
            if(!label.innerText.match(new RegExp(search, 'i'))) {
                label.classList.add('hidden');
            }
        });
    }



    addAutoParentSelect() {
        this.categoryCheckboxElements.forEach((checkbox) => {
           checkbox.addEventListener('change', async (e) => {
               let checked = e.target.checked;
               if(checked) {
                  await this.changeAllowedAttributeIds();
               }
               if(checkbox.parentElement.classList.contains('firstLevel')) {
                   if(!checked) {
                       this.uncheckChildCategories(checkbox);
                       return;
                   }
               }
               if(checkbox.parentElement.classList.contains('secondLevel')) {
                   let parent = this.getCheckboxParentByLevel('second',checkbox);
                   if(checked) {
                       if(!parent.checked) {
                           parent.click();
                       }
                       return;
                   }
                   this.uncheckChildCategories(checkbox);
                   return;
               }
               if(checkbox.parentElement.classList.contains('thirdLevel')) {
                   let parent = this.getCheckboxParentByLevel('third',checkbox);
                   if(checked) {
                       if(!parent.checked) {
                           parent.click();
                       }
                   }
               }
           });
        });
    }

    async changeAllowedAttributeIds() {
        let categoryCheckboxes = document.querySelectorAll('.categoryList input[name=category]:checked');
        let category = categoryCheckboxes[categoryCheckboxes.length - 1];
        let action = '/attribute/getAttributesForCategory/';
        let formData = new FormData();
        formData.append('categoryId', category.value)
        let req = await fetch(action, {
            method: 'POST',
            body: formData
        });
        let attributeIds =  JSON.parse(await req.text());
        let ids = [];
        for(let key of Object.keys(attributeIds.attributeIds)) {
            ids.push(attributeIds.attributeIds[key].attributeId);
        }
        document.getElementById('attributes').setAttribute('data-allowed-attribute-ids', ids.join());
        this.generateSecondDescription();
    }


    async handleCategoryChange(checkbox) {
        let categoryId = checkbox.value;
        let attributeIds = await this.getAttributeIdsForCategory(categoryId);
        if(attributeIds.length === 0) {
            return;
        }
        if(checkbox.checked) {
            if(confirm('This category has an attribute group assigned, do you want to apply the group')) {
                for(let attributeId of attributeIds) {
                    let existingAttributeValues = null;
                    if(this.productAttributes[attributeId]) {
                        existingAttributeValues = this.productAttributes[attributeId].existingAttributeValues;
                        this.productAttributes[attributeId].deleteComponent();
                    }
                    let option = document.querySelector(`#attributeSelect option[value="${attributeId}"]`);
                    await this.addNewAttributeComponent(attributeId, option.innerText, existingAttributeValues);
                }
                this.generateSecondDescription();
            }

        } else {
            this.deleteAttributesByAttributeIds(attributeIds)
        }
    }

    deleteAttributesByAttributeIds(attributeIds)
    {
        for(let attributeId of attributeIds) {
            if(this.productAttributes[attributeId]) {
                this.productAttributes[attributeId].deleteComponent();
            }
        }
    }

    async getAttributeIdsForCategory(categoryId) {
        let action = '/attribute-group/getAttributesFromGroupByCategory/';
        let formData = new FormData();
        formData.append('categoryId', categoryId)
        let req = await fetch(action, {
            method: 'POST',
            body: formData
        });
        let entities =  JSON.parse(await req.text());
        let attributeIds = [];
        entities.entities.forEach((entity) => {
            attributeIds.push(entity.attributeId);
        });
        return [...new Set(attributeIds)]; // Remove duplicate entries from array
    }

    getCheckboxParentByLevel(level, checkbox) {
        switch(level) {
            case 'first':
                return checkbox.parentElement;
            case 'second':
                return checkbox.parentElement.parentElement.parentElement.querySelector('input');
            case 'third':
                return checkbox.parentElement.parentElement.querySelector('.secondLevel input');
        }
    }

    uncheckChildCategories(checkbox) {
        checkbox.parentElement.parentElement.querySelectorAll('input[type="checkbox"]:checked').forEach((checkedBox) => {
            checkedBox.checked = false;
        });
    }

    addTabControl() {
        let tabs = this.modal.getInnerModal().querySelectorAll('.tab');
        tabs.forEach((tab) => {
           tab.addEventListener('click', () => {
               this.activateTab(tab);
           });
        });
    }

    activateTab(selectedTab) {
        let tabs = this.modal.getInnerModal().querySelectorAll('.tab');
        tabs.forEach((tab) => {
            if(tab === selectedTab) {
                selectedTab.classList.add('active');
                this.activateTabContent(document.getElementById(tab.getAttribute('data-target')))
                return;
            }
            tab.classList.remove('active');
        });
    }

    activateTabContent(targetTabContent) {
        let tabContentElements = this.modal.getInnerModal().querySelectorAll('.tabContent');
        tabContentElements.forEach((tabContent) => {
            if(targetTabContent === tabContent) {
                targetTabContent.classList.remove('hidden');
                return;
            }
            tabContent.classList.add('hidden');
        });
    }

    // Attributes start
    addAttributeListeners() {
        this.generateSearchForAttributes();
        this.removeAttributeSearchListener();
        this.addAttributeButtonListener();
        this.addSortable();
        this.attributeDeletedListener();
        this.addHideResultsListener();
        this.addNewAttributeListener();
        // this.addExistingAttributes().then(() => {
        //     document.getElementById('submitButton').disabled = false;
        //     let attributesMainContainer = document.getElementById('attributes');
        //     attributesMainContainer.style.opacity = '1';
        //     this.stopLoaderForAttribute();
        //     this.generateSecondDescriptionOnLoad();
        //     this.generateSecondDescriptionOnAttributeChange();
        // });
    }

    startLoaderForAttribute(container, loaderSize) {
        if(!document.getElementById('attributeLoader')) {
            container.appendChild(this.generateLoaderForAttribute(loaderSize));
        }
    }

    stopLoaderForAttribute() {
        let loader = document.getElementById('attributeLoader');
        if(loader) {
            loader.remove();
        }
    }

    generateLoaderForAttribute(loaderSize) {
        let loader = document.createElement('div');
        loader.id = 'attributeLoader';
        loader.classList.add(loaderSize);
        return loader;
    }

    async addExistingAttributes() {
        let attributesMainContainer = document.getElementById('attributes');
        attributesMainContainer.style.opacity = '0';
        this.startLoaderForAttribute(document.querySelector('.attributeTab'),'large');
        let existingContainers = document.querySelectorAll('.existingAttribute');
        for await (let container of existingContainers) {
            let attributeId = container.getAttribute('data-attribute-id');
            let position = container.getAttribute('data-position');
            let productAttributeValues = await this.getProductAttributeValues(attributeId);
            let innerExisting = container.querySelectorAll('div');
            let attributeName = innerExisting[0].getAttribute('data-attribute-name');

            let existingAttrValues = {};
            innerExisting.forEach((inner,index) => {
                existingAttrValues[index] = {
                    attributeId: inner.getAttribute('data-attribute-id'),
                    attributeValueId: inner.getAttribute('data-attribute-value-id'),
                    attributeValue: inner.getAttribute('data-attribute-value'),
                    attributeName: inner.getAttribute('data-attribute-name')
                };
            });
            this.productAttributes[attributeId] = new ProductAttributeComponent(
                attributeId,
                attributeName,
                productAttributeValues,
                this.productAttributeDeletedEventName,
                this.regenerateSecondDescriptionEventName,
                null,
                this.form,
                existingAttrValues,
                position
            );
        }
    }

    addSortable() {
        $( function() {
            $("#attributes").sortable();
        } );

        $( function () {
            $("#galleryPreview").sortable({
                flow: 'horizontal',
                stop: function(event, ui) {
                    document.querySelectorAll('#galleryPreview > div').forEach((image,index) => {
                        image.setAttribute('data-position', index.toString());
                        let input = image.querySelector('input');
                        if(input) {
                            input.value = index.toString();
                        }
                    });
                }
            });
        })
    }

    attributeDeletedListener() {
        document.getElementById('attributes').addEventListener(this.productAttributeDeletedEventName, (e) => {
            let attributeId = e.detail;
            if (this.productAttributes[attributeId]) {
                delete this.productAttributes[attributeId];
            }
            this.generateSecondDescription();
        });
    }

    addHideResultsListener() {
        document.getElementById('modalContent').addEventListener('click', (e) => {
            let results = document.querySelector('.attributeResults');
            if(results && !e.target.classList.contains('contentBody') && !e.target.classList.contains('attributeValueSearch')
            && !e.target.classList.contains('disabled') && !e.target.classList.contains('attributeResults')) {
                for(let key of Object.keys(this.productAttributes)) {
                    this.productAttributes[key].resultsDestroy();
                }
            }
        });
    }


    addAttributeButtonListener() {
        let selectElem = document.getElementById('attributeSelect');
        document.getElementById('addAttributeFromSelect').addEventListener('click', async (e) => {
            e.preventDefault();
            if(document.activeElement.classList.contains('attributeValueSearch')) {
                return;
            }
            let allowedAttribute = this.allowedAttribute(selectElem.value);
            if(allowedAttribute !== true) {
                alert(allowedAttribute);
                return;
            }
            let selectedOption = selectElem.options[selectElem.selectedIndex];
            await this.addNewAttributeComponent(parseInt(selectElem.value), selectedOption.text, null, selectedOption.getAttribute('data-position'));
        });
    }

    async addNewAttributeComponent(attributeId, attributeName, existingAttributeValues = null, position = null) {
        let productAttributeValues = await this.getProductAttributeValues(attributeId);
        this.productAttributes[attributeId] = new ProductAttributeComponent(
            attributeId,
            attributeName,
            productAttributeValues,
            this.productAttributeDeletedEventName,
            this.regenerateSecondDescriptionEventName,
            null,
            this.form,
            existingAttributeValues,
            position,
            true
        );
    }

    async getProductAttributeValues(attributeId) {
        let action = '/attribute/getAttributeValues/';
        let data = new FormData();
        data.append('attributeId', attributeId);
        let req = await fetch(action, {
            method: 'POST',
            body: data
        });
        return JSON.parse(await req.text());
    }

    allowedAttribute(selectValue) {
        if(selectValue === '-1') {
            return 'Please select an attribute';
        }
        if(this.productAttributes[parseInt(selectValue)]) {
            return 'Attribute already exists';
        }
        return true;
    }

    addNewAttributeListener() {
        this.createNewAttributeButton.addEventListener('click', (e) => {
           e.preventDefault();
           let newAttribute = new ProductAttributeComponent(
               null,null,
               null,
               null,
               null,
               this.newAttributeCounter,
               this.form
               );
           this.newAttributeCounter++;
        });
    }




    generateSecondDescriptionOnLoad() {
        if(document.getElementById('shortDescriptionInput').value === '') {
            this.generateSecondDescription()
        }
    }

    generateSecondDescriptionOnAttributeChange() {
        document.getElementById('attributes').addEventListener(this.regenerateSecondDescriptionEventName, () => {
            this.generateSecondDescription();
        });
    }

    generateSecondDescription() {
        let container = document.getElementById('attributes');
        let allowedAttributeIds = container.getAttribute('data-allowed-attribute-ids').split(',');
        let html = '<table class="woocommerce-product-attributes shop_attributes"><tbody>';
        let hasContent = false;
        allowedAttributeIds.forEach((allowedAttributeId) => {
            let reference = this.productAttributes[allowedAttributeId];
            if(reference) {
                html += `<tr><th>${reference.attributeName}</th>`;
                html += `<td>`;
                let count = 1;
                let length = Object.keys(reference.selectedAttributeValues).length
                for (let key of Object.keys(reference.selectedAttributeValues)) {
                    html += reference.selectedAttributeValues[key];
                    if(count !== length) {
                        html += ', ';
                    }
                    count++;
                    hasContent = true;
                }
                html += '</td></tr>';
            }
        });
        html += '</tbody></table>';

        if(!hasContent) {
            html = '';
        }
        document.getElementById('shortDescription').innerHTML = html;
        document.getElementById('shortDescriptionInput').value = html;
        tinymce.get('shortDescription').setContent(html);
    }

    // Attributes end


    addGalleryImagesUpload() {
        let mediaUploader = new MediaUploader();
        mediaUploader.registerNewUploader({
            inputId: 'multipleFileInput',
            targetId: 'galleryImageUpload',
            triggerCustomEvents: false,
            appearance: {
                windowOnDragTargetClass: 'windowOnDragTargetClass',
                onTargetDragOverClass: 'onTargetDragOverClass'
            },
            preview: {
                previewContainerId: 'galleryPreview',
                previewImageContainerClass: 'previewImage',
                imageAltAttributeValue: 'image preview',
                removeImageIcon: {
                    html: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>`,
                    iconWidth: '32px',
                    iconHeight: '32px',
                    iconColor: 'red',
                    iconPositionTop: '0',
                    iconPositionRight: '0',
                }
            }
        });
    }

    scrollToSelectedCategory() {
        let checkedBox = document.querySelector('input[name=category]:checked');
        if(checkedBox) {
            checkedBox.scrollIntoView({behavior: 'auto', block:'nearest', inline: 'start'});
            let parentScrollableElement = document.querySelector('form .scrollableContainer');
            parentScrollableElement.scrollTop += parentScrollableElement.clientHeight / 2;
        }
    }

    generateSearchForCategoryFilter() {
        let categoryFilter = document.querySelector('.filterWrapper select[name="categoryFilter"]');
        let parent = categoryFilter.parentElement;
        let container = document.createElement('div');
        container.classList.add('searchCategoryContainer');
        let searchButton = document.createElement('div');
        searchButton.classList.add('searchCategory');
        searchButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                  </svg>`
        container.appendChild(searchButton);
        parent.appendChild(container);
        let clearCategoryFilter = document.createElement('div');
        clearCategoryFilter.style.cursor = 'pointer';
        clearCategoryFilter.style.position ='absolute';
        clearCategoryFilter.style.top ='0';
        clearCategoryFilter.style.right ='0';
        clearCategoryFilter.innerHTML = `<svg style="pointer-events:none; width:24px; color:red;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        `;
        container.parentElement.style.position = 'relative';

        clearCategoryFilter.addEventListener('click', () => {
            document.querySelector('select[name="categoryFilter"]').value = '-1';
            const url = new URL(window.location.href);
            url.searchParams.delete('category');
            let newUrl = url.href.replace(/\+/g,"%20"); // replace + with encode
            window.history.pushState(null, null, newUrl);
            this.crudTable.setupTable(0, null);
        });
        parent.appendChild(clearCategoryFilter);


        let checkboxContainerSubCats = document.createElement('div');
        checkboxContainerSubCats.style.display = 'flex';
        checkboxContainerSubCats.style.alignItems = 'center';
        checkboxContainerSubCats.style.gap = '1rem';
        let label = document.createElement('label');
        label.style.marginBottom = '0';
        label.innerText = 'Include sub categories';
        let includeChildCategoriesCheckbox = document.createElement('input');
        includeChildCategoriesCheckbox.type = 'checkbox';
        includeChildCategoriesCheckbox.name = 'includeChildCategories';

        checkboxContainerSubCats.appendChild(includeChildCategoriesCheckbox);
        checkboxContainerSubCats.appendChild(label);

        parent.appendChild(checkboxContainerSubCats);

        this.addIncludeChildCategoriesCheckboxListener(includeChildCategoriesCheckbox);
        this.getMasterCategories().then((masterCategories) => {
            this.addSearchListeners(masterCategories);
        });
    }

    addIncludeChildCategoriesCheckboxListener(includeChildCategoriesCheckbox) {
        includeChildCategoriesCheckbox.addEventListener('change', () => {
            if(includeChildCategoriesCheckbox.checked) {
                this.crudTable.setCustomFilterData(includeChildCategoriesCheckbox.name, 'true', true);
                return;
            }
            this.crudTable.deleteCustomFilterData(includeChildCategoriesCheckbox.name, true);
        });
    }

    generateSearchForAttributes() {
        let attributeSelect = document.getElementById('attributeSelect');
        let parent = attributeSelect.parentElement;
        let container = document.createElement('div');
        container.classList.add('searchAttributeContainer');
        let searchButton = document.createElement('div');
        searchButton.classList.add('searchAttributesButton');
        searchButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                  </svg>`
        container.appendChild(searchButton);
        parent.appendChild(container);
        this.addSearchAttributeListener();
    }

    addSearchAttributeListener() {
        let searchButton = document.querySelectorAll('.searchAttributesButton');
        searchButton.forEach((button) => {
            button.addEventListener('click', () => {
                if(document.getElementById('attributeSearchInput')) {
                    return;
                }
                let parent = button.parentElement;
                let selectElement = parent.parentElement.querySelector('select');
                let search = this.generateAttributeSearch();
                parent.appendChild(search);
                search.querySelector('input').focus();
                search.querySelector('.scrollableContainerCustomHeight').appendChild(this.generateAttributeResults(this.attributes, selectElement));
            });
        });
    }

    generateAttributeSearch() {
        let container = document.createElement('div');
        container.classList.add('attributeSearchInputContainer')
        let searchInput = document.createElement('input');
        searchInput.classList.add('form-control');
        searchInput.id = 'attributeSearchInput';

        let resultsContainer = document.createElement('div');
        resultsContainer.classList.add('scrollableContainerCustomHeight');

        let timeout = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.filterResults(searchInput.value, resultsContainer.querySelectorAll('p'));
            },200)

        });
        container.appendChild(searchInput);
        container.appendChild(resultsContainer);
        return container;
    }

    generateAttributeResults(attributes, selectElement) {
        let wrapper = document.createElement('div');
        attributes.forEach((attribute) => {
            let resultElem = document.createElement('p');
            resultElem.innerText = attribute.attributeName;
            resultElem.setAttribute('id', attribute.attributeId);
            resultElem.addEventListener('click', () => {
                selectElement.value = attribute.attributeId.toString();
                selectElement.dispatchEvent(new Event('change'));
                this.removeAttributeSearch();
            });
            wrapper.appendChild(resultElem);
        });
        return wrapper;
    }

    removeAttributeSearch() {
        document.getElementById('attributeSearchInput').parentElement.remove();
    }

    removeAttributeSearchListener() {
        document.body.addEventListener('mousedown', (e) => {
            let inputSearchContainer = document.getElementById('attributeSearchInput')?.parentElement.parentElement;
            if(inputSearchContainer && !inputSearchContainer.contains(e.target)) {
                this.removeAttributeSearch();
            }
        });
    }


    addAttributesGroupListener() {
        let attributeGroupSelect = document.getElementById('attributeGroupSelect');
        attributeGroupSelect.addEventListener('change', async() => {
            let attrIds = attributeGroupSelect.value.split(',');
            let selectElemOptions = document.getElementById('attributeSelect').options;
            let selectedOption = null
            for await (let attrId of attrIds) {
                for(let i = 0; i < selectElemOptions.length; i++) {
                    if(selectElemOptions[i].value == attrId) {
                        selectedOption = selectElemOptions[i];
                        break;
                    }
                }
                let allowedAttribute = this.allowedAttribute(selectedOption.value);
                if(allowedAttribute !== true) {
                    alert(allowedAttribute + ' ' + selectedOption.text);
                } else {
                    if (selectedOption) {
                        await this.addNewAttributeComponent(parseInt(attrId), selectedOption.text, null, selectedOption.getAttribute('data-position'));
                    }
                }
            }
        });
    }

    addBulkSalePriceListeners() {
        //only Sale price or Fictional price can be set not both
        let salePriceInput = document.getElementById('specialPricePercentage');
        let fictionalPriceInput = document.getElementById('fictionalSalePercentage');
        let salePriceFrom = document.getElementById('specialPriceFrom');
        let salePriceTo = document.getElementById('specialPriceTo');
        let submitButton = document.getElementById('submitButton');
        salePriceInput.addEventListener('input', () => {
            if(salePriceInput.value && salePriceInput.value != 0) {
                fictionalPriceInput.value = '';
                if (!salePriceFrom.value){
                    salePriceFrom.style.borderColor = 'red';
                    // submitButton.disabled = true;
                }
                if (!salePriceTo.value){
                    salePriceTo.style.borderColor = 'red';
                    // submitButton.disabled = true;
                }
            } else {
                if (!fictionalPriceInput.value){
                    salePriceFrom.style.borderColor = '';
                    salePriceTo.style.borderColor = '';
                    // submitButton.disabled = false;
                }
            }
        });
        fictionalPriceInput.addEventListener('input', () => {
            if(fictionalPriceInput.value && fictionalPriceInput.value != 0) {
                salePriceInput.value = '';
                if (!salePriceFrom.value){
                    salePriceFrom.style.borderColor = 'red';
                    // submitButton.disabled = true;
                }
                if (!salePriceTo.value){
                    salePriceTo.style.borderColor = 'red';
                    // submitButton.disabled = true;
                }
            }else {
                if (!salePriceInput.value){
                    salePriceFrom.style.borderColor = '';
                    salePriceTo.style.borderColor = '';
                    // submitButton.disabled = false;
                }
            }
        });
        salePriceFrom.addEventListener('input', () => {
            if (salePriceFrom.value){
                salePriceFrom.style.borderColor = '';
                if (salePriceTo.value){
                    // submitButton.disabled = false;
                }
            } else {
                submitButton.disabled = true;
                salePriceFrom.style.borderColor = 'red';
            }
        });
        salePriceTo.addEventListener('input', () => {
            if (salePriceTo.value){
                salePriceTo.style.borderColor = '';
                if (salePriceFrom.value){
                    // submitButton.disabled = false;
                }
            }  else {
                submitButton.disabled = true;
                salePriceTo.style.borderColor = 'red';
            }
        });
    }

    addSalePriceListeners() {
        //only Sale price or Fictional price can be set not both
        let salePriceInput = document.getElementById('specialPrice');
        let fictionalPriceInput = document.getElementById('fictionalSalePercentage');
        let salePriceFrom = document.getElementById('specialPriceFrom');
        let salePriceTo = document.getElementById('specialPriceTo');
        let submitButton = document.getElementById('submitButton');
        // salePriceInput.addEventListener('input', () => {
        //     if(salePriceInput.value && salePriceInput.value != 0) {
        //         fictionalPriceInput.value = 0;
        //         if (!salePriceFrom.value){
        //             salePriceFrom.style.borderColor = 'red';
        //             submitButton.disabled = true;
        //         }
        //         if (!salePriceTo.value){
        //             salePriceTo.style.borderColor = 'red';
        //             submitButton.disabled = true;
        //         }
        //     } else {
        //         if (!fictionalPriceInput.value || fictionalPriceInput.value == 0){
        //             salePriceFrom.style.borderColor = '';
        //             salePriceTo.style.borderColor = '';
        //             submitButton.disabled = false;
        //         }
        //     }
        // });
        // fictionalPriceInput.addEventListener('input', () => {
        //     if(fictionalPriceInput.value && fictionalPriceInput.value != 0) {
        //         salePriceInput.value = 0;
        //         if (!salePriceFrom.value || salePriceFrom.value == 0){
        //             salePriceFrom.style.borderColor = 'red';
        //             submitButton.disabled = true;
        //         }
        //         if (!salePriceTo.value){
        //             salePriceTo.style.borderColor = 'red';
        //             submitButton.disabled = true;
        //         }
        //     }else {
        //         if (!salePriceInput.value || salePriceInput.value == 0){
        //             salePriceFrom.style.borderColor = '';
        //             salePriceTo.style.borderColor = '';
        //             submitButton.disabled = false;
        //         }
        //     }
        // });
        // salePriceFrom.addEventListener('input', () => {
        //     if (salePriceFrom.value){
        //         salePriceFrom.style.borderColor = '';
        //         if (salePriceTo.value){
        //             submitButton.disabled = false;
        //         }
        //     } else {
        //         if (salePriceInput.value && salePriceInput.value != 0){
        //             submitButton.disabled = true;
        //             salePriceFrom.style.borderColor = 'red';
        //         }
        //     }
        // });
        // salePriceTo.addEventListener('input', () => {
        //     if (salePriceTo.value){
        //         salePriceTo.style.borderColor = '';
        //         if (salePriceFrom.value){
        //             submitButton.disabled = false;
        //         }
        //     }  else {
        //         if (salePriceInput.value && salePriceInput.value != 0) {
        //             submitButton.disabled = true;
        //             salePriceTo.style.borderColor = 'red';
        //         }
        //     }
        // });
    }

    addLoopSalePriceListener() {
        let checkbox = document.getElementById('salePriceLoop');
        let salePriceFrom = document.getElementById('specialPriceFrom');
        let salePriceTo = document.getElementById('specialPriceTo');
        checkbox.addEventListener('change', (e) => {
            if(e.target.checked) {
                let days = Math.ceil((new Date(salePriceTo.value) - new Date(salePriceFrom.value)) / (1000 * 60 * 60 * 24));
                if (days > 0) {
                    alert('The sale price will renew every ' + days + ' days.');
                }
            }
        })
    }
}
