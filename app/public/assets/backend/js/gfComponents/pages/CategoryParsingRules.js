import BasePage from "../components/BasePage.js";
import SimpleAjaxSearch from "../utils/SimpleAjaxSerarch.js";
import MultipleSelectWithSearch from "../utils/MultipleSelectWithSearch.js";

export default class CategoryParsingRules extends BasePage {

    constructor(crudTable) {
        super(crudTable);
        this.modalStyleConfig = {
            create: {
                modalWidth: '800px',
                modalHeight: '800px'
            },
            edit: {
                modalWidth: '800px',
                modalHeight: '800px'
            }
        }
        this.addAdditionalListeners();
    }

    addAdditionalListeners() {
        this.addSearchToCategoryFilter();
        this.addIncludeSubcategoriesCheckbox();
        this.addCustomFilterData();
        this.crudTable.crudTable.addEventListener(this.crudTable.tablePopulatedEventName, () => {
            this.addRuleActionsListeners();
        });
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addActionChangeListener();
            let categorySearchHandler = new MultipleSelectWithSearch('ruleCategorySearch', this.itemClickFilter);
            categorySearchHandler.init();
            categorySearchHandler.setLimit(50);
            categorySearchHandler.setFilters({tenantId: 0});
        });
    }

    itemClickFilter(resultItem)
    {
        let actionSelect = document.getElementById('action');
        if (actionSelect.value == 'EcomHelper\\Feeder\\ParsingActions\\ChangeCategoryBasedOnString') {
            let resultItems = document.querySelectorAll('.searchInputs[data-searchid="ruleCategorySearch"] > div');
            if (resultItems.length > 1) {
                alert('Notice, you can only select one category for this action');
            }
        }
        return true;
    }

    addActionChangeListener() {
        let select = document.getElementById('action');
        let container = document.getElementById('formInputs');
        if (select.value != 0) {
            let actionUrl = select.options[select.selectedIndex].getAttribute('data-action');
            this.fetcher.fetchData(actionUrl, 'GET').then((data) => {
                container.innerHTML = data;
                select.dispatchEvent(new Event('change'));
                this.handleDescriptionGeneration(actionUrl);
            });
        }
        select.addEventListener('change', (e) => {
            if (e.target.selectedIndex === 0) {
                container.innerHTML = '';
                return;
            }
            let actionUrl = e.target.options[e.target.selectedIndex].getAttribute('data-action');
            if (select.value === 'EcomHelper\\Feeder\\ParsingActions\\ChangeCategoryBasedOnString') {
                let resultItems = document.querySelectorAll('.searchInputs[data-searchid="ruleCategorySearch"] > div');
                if (resultItems.length > 1) {
                    alert('Notice, you can only select one category for this action');
                }
            }
            this.fetcher.fetchData(actionUrl, 'GET').then((data) => {
                container.innerHTML = data;
                this.addChangeCategoryBasedOnStringListeners();
                this.addCreateAttributeBasedOnStringListeners();
                this.handleDescriptionGeneration(actionUrl);
            });
        });
    }

    addRuleActionsListeners() {
        let applyNow = document.querySelectorAll('.applyRuleNow');
        applyNow.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                let actionUrl = e.target.getAttribute('data-action');
                e.target.disabled = true;
                this.fetcher.fetchData(actionUrl, 'GET').then((data) => {
                    alert(data);
                    e.target.disabled = false;
                });
            });
        });
        let undoRule = document.querySelectorAll('.undoRule');
        undoRule.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                let actionUrl = e.target.getAttribute('data-action');
                e.target.disabled = true;
                this.fetcher.fetchData(actionUrl, 'GET').then((data) => {
                    alert(data);
                    e.target.disabled = false;
                });
            });
        });
    }
    addCategorySearchListener(containerClass, searchIconWrapperClass, callback) {
        let container = document.querySelector(`.${containerClass}`);
        let searchWrapper = document.querySelector(`.${searchIconWrapperClass}`);
        let ajaxSearch = new SimpleAjaxSearch({
            initiator: searchWrapper,
            placeholder: 'Search categories',
            dispatchTank: container,
            resultClickedEventName: 'categorySelected',
            limit: 20,
            charactersSearchThreshold: 2,
            delayOnSearchInMs: 300,
            resultViewParameter: 'name',
            noMoreResultsMessage: 'No more categories',
            filters: {tenantId: 0}
        });
        ajaxSearch.mount();
        container.addEventListener('categorySelected', (e) => {
            callback(e);
        });
    }

    addSearchToCategoryFilter() {
        let categoryFilter = document.querySelector('select[name="categoriesFilter"]');
        categoryFilter.classList.add('selectWithSearchFilter');
        let container = categoryFilter.parentElement;
        let searchWrapper = document.createElement('div');
        searchWrapper.dataset.action = '/category/tableHandler/';
        searchWrapper.style.cursor = 'pointer';
        searchWrapper.classList.add('searchIconWrapperFilter');
        searchWrapper.innerHTML = `<svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                                </svg>`;
        container.appendChild(searchWrapper);
        this.addCategorySearchListener('selectWithSearchFilter', 'searchIconWrapperFilter', (e) => {
            categoryFilter.value = e.detail.id;
            categoryFilter.dispatchEvent(new Event('change'));
        });
    }

    addAttributeSearchListener(containerClass, searchIconWrapperClass, callback) {
        let container = document.querySelector(`.${containerClass}`);
        let searchWrapper = document.querySelector(`.${searchIconWrapperClass}`);
        let ajaxSearch = new SimpleAjaxSearch({
            initiator: searchWrapper,
            placeholder: 'Search Attributes',
            dispatchTank: container,
            resultClickedEventName: 'attributeSelected',
            limit: 20,
            charactersSearchThreshold: 2,
            delayOnSearchInMs: 300,
            resultViewParameter: 'attributeName',
            noMoreResultsMessage: 'No more attributes',
        });
        ajaxSearch.mount();
        container.addEventListener('attributeSelected', (e) => {
            callback(e);
        });
    }

    addAttributeValuesSearchListener(containerClass, searchIconWrapperClass, callback) {
        let container = document.querySelector(`.${containerClass}`);
        let searchWrapper = document.querySelector(`.${searchIconWrapperClass}`);
        let ajaxSearch = new SimpleAjaxSearch({
            initiator: searchWrapper,
            placeholder: 'Search Attribute values',
            dispatchTank: container,
            resultClickedEventName: 'attributeValueSelected',
            limit: 20,
            charactersSearchThreshold: 2,
            delayOnSearchInMs: 300,
            resultViewParameter: 'name',
            noMoreResultsMessage: 'No more values',
        });
        ajaxSearch.mount();
        container.addEventListener('attributeValueSelected', (e) => {
            callback(e);
        });
    }

    addChangeCategoryBasedOnStringListeners() {
        if (document.querySelector('.selectWithSearchCBOS')) {
            this.addCategorySearchListener('selectWithSearchCBOS', 'searchContentCBOS', (e) => {
                let select = document.getElementById('searchCategoryCBOS');
                select.value = e.detail.id;
                select.dispatchEvent(new Event('change'));
            });
        }
    }

    addCreateAttributeBasedOnStringListeners() {
        if (document.querySelector('.searchAttributeAABOS')) {
            this.addAttributeSearchListener('searchAttributeAABOS', 'searchAttributeContentAABOS', (e) => {
                let select = document.getElementById('searchAttributeAABOS');
                select.value = e.detail.id;
                select.dispatchEvent(new Event('change'));
            });
        }
        if(document.getElementById('searchAttributeAABOS')) {
            document.querySelector('#searchAttributeAABOS').addEventListener('change', (e) => {
                let select = document.getElementById('searchAttributeAABOS');
                let actionUrl = select.options[select.selectedIndex].getAttribute('data-fetch-value-action');
                //fetch attribute values and append select for them
                this.fetcher.fetchData(actionUrl, 'GET').then((data) => {
                    let valuesSelect = document.getElementById('searchAttributeValueAABOS');
                    let defaultOption = document.createElement('option');
                    valuesSelect.innerHTML = '';
                    defaultOption.value = '-1';
                    defaultOption.innerHTML = '------';
                    valuesSelect.appendChild(defaultOption);
                    JSON.parse(data).forEach((option) => {
                        let optionElement = document.createElement('option');
                        optionElement.value = option.id;
                        optionElement.innerHTML = option.value;
                        valuesSelect.appendChild(optionElement);
                    });
                //add search to attribute values select
                    if (document.querySelector('.searchAttributeValueAABOS')) {
                        this.addAttributeValuesSearchListener('searchAttributeValueAABOS', 'searchAttributeValueContentAABOS', (e) => {
                            let select = document.getElementById('searchAttributeValueAABOS');
                            select.value = e.detail.id;
                            select.dispatchEvent(new Event('change'));
                        });
                    }
                });
            });
        }
    }

    addIncludeSubcategoriesCheckbox() {
        let select = document.querySelector('select[name="categoriesFilter"]');
        let container = select.parentElement;
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
        container.appendChild(checkboxContainerSubCats);
        this.addIncludeChildCategoriesCheckboxListener(includeChildCategoriesCheckbox);
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

    handleDescriptionGeneration(actionUrl) {
        let description = document.querySelector('textarea[name="description"]');
        if (actionUrl.includes('SearchReplace')) {
            let searchInput = document.querySelector('input[name="data[search]"]');
            let replaceInput = document.querySelector('input[name="data[replace]"]');
            let subjectSelect = document.querySelector('select[name="data[subject]"]');
            let getSubject = () => {
                let subject = subjectSelect.value
                if (subject === '-1') {
                    subject = '______';
                }
                return subject;
            }
            let getSearch = () => {
                let search = searchInput.value;
                if (search === '') {
                    search = '______';
                }
                return search;
            }
           let getReplace = () => {
               let replace = replaceInput.value;
               if (replace === '') {
                   replace = '______';
               }
               return replace;
           }

            searchInput.addEventListener('input', () => {
                description.value = `Search for [${getSearch()}] and replace with [${getReplace()}] in product [${getSubject()}]`;
            });
            replaceInput.addEventListener('input', () => {
                description.value = `Search for [${getSearch()}] and replace with [${getReplace()}] in product [${getSubject()}]`;
            });
            subjectSelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] and replace with [${getReplace()}] in product [${getSubject()}]`;
            });
        }
        if (actionUrl.includes('ChangeCategoryBasedOnString')) {
            let searchInput = document.querySelector('input[name="data[search]"]');
            let categorySelect = document.querySelector('select[name="data[category]"]');
            let subjectSelect = document.querySelector('select[name="data[subject]"]');
            let getSubject = () => {
                let subject = subjectSelect.value
                if (subject === '-1') {
                    subject = '______';
                }
                return subject;
            }
            let getSearch = () => {
                let search = searchInput.value;
                if (search === '') {
                    search = '______';
                }
                return search;
            }

            let getCategory = () => {
                let category = categorySelect.value;
                let categoryName = categorySelect.options[categorySelect.selectedIndex].innerText;
                if (category === '-1') {
                    categoryName = '______';
                }
                return categoryName.trim();
            }
            searchInput.addEventListener('input', () => {
                console.log('search input changed');
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, change category to [${getCategory()}]`;
            });
            subjectSelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, change category to [${getCategory()}]`;
            });
            categorySelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, change category to [${getCategory()}]`;
            });
        }
        if (actionUrl.includes('AddAttributeBasedOnString')) {
            let searchInput = document.querySelector('input[name="data[search]"]');
            let subjectSelect = document.querySelector('select[name="data[subject]"]');
            let attributeSelect = document.querySelector('select[name="data[attributeId]"]');
            let attributeValuesSelect = document.querySelector('select[name="data[attributeValueId]"]');


            let getSubject = () => {
                let subject = subjectSelect.value
                if (subject === '-1') {
                    subject = '______';
                }
                return subject;
            }
            let getSearch = () => {
                let search = searchInput.value;
                if (search === '') {
                    search = '______';
                }
                return search;
            }

            let getAttribute = () => {
                let attribute = attributeSelect.value;
                let attributeName = attributeSelect.options[attributeSelect.selectedIndex].innerText;
                if (attribute === '-1') {
                    attributeName = '______';
                }
                return attributeName.trim();
            }

            let getAttributeValue = () => {
                let attributeValue = attributeValuesSelect.value;
                let attributeValueName = attributeValuesSelect.options[attributeValuesSelect.selectedIndex].innerText;
                if (attributeValue === '-1') {
                    attributeValueName = '______';
                }
                return attributeValueName.trim();
            }

            searchInput.addEventListener('input', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, add attribute [${getAttribute()}] with value [${getAttributeValue()}]`;
            });
            subjectSelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, add attribute [${getAttribute()}] with value [${getAttributeValue()}]`;
            });
            attributeSelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, add attribute [${getAttribute()}] with value [${getAttributeValue()}]`;
            });
            attributeValuesSelect.addEventListener('change', () => {
                description.value = `Search for [${getSearch()}] in product [${getSubject()}], if found, add attribute [${getAttribute()}] with value [${getAttributeValue()}]`;
            });
        }
    }

    addCustomFilterData()
    {
        //check if get contains param ids
        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('ids')) {
            let ids = urlParams.get('ids');
            this.crudTable.setCustomFilterData('ids', ids);
        }
    }
}

