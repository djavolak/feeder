import BasePage from "../components/BasePage.js";
import TheSimplestAjaxSearch from "../utils/TheSimplestAjaxSearch.js";

export default class AttributeMapping extends BasePage {
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
        };
        this.addAdditionalListeners();
    }

    addAdditionalListeners() {
        this.addSupplierSelectListener();
        this.addAttributeSelectListener();
        this.initFilterAjaxSearch();
        this.iframeModalStuff();
        this.addCategorySelectListener();
        this.crudTable.crudTable.addEventListener(this.crudTable.tablePopulatedEventName, () => {
            this.applyMappingButtonListener();
            this.undoMappingButtonListener();
        });
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addLocalAttributeSelectListener();
            this.addAttributeEditListener();
            new TheSimplestAjaxSearch('ajaxSearchForm').init();
            this.addNewLocalAttributeListener();
            this.initDeleteButtonsListener();
        });
    }

    addSupplierSelectListener() {
        let supplierSelect = document.querySelector('select[name="supplierFilter"]');
        let attributeSelect = document.querySelector('select[name="attributeFilter"]');
        let attributeValueSelect = document.querySelector('select[name="attributeValueFilter"]');
        let categorySelect = document.querySelector('select[name="categoryFilter"]');
        attributeValueSelect.style.pointerEvents = 'none';
        attributeValueSelect.style.backgroundColor = '#eee';
        attributeSelect.style.pointerEvents = 'none';
        attributeSelect.style.backgroundColor = '#eee';
        categorySelect.style.pointerEvents = 'none';
        categorySelect.style.backgroundColor = '#eee';
        supplierSelect.addEventListener('change', () => {
            let supplierId = supplierSelect.value;
            //fetch categories
            this.fetcher.fetchData(`/attribute-mapping/getCategoriesForSupplier/?id=${supplierId}`, 'GET').then((data) => {
                let options = '<option value="-1">---</option>';
                data = JSON.parse(data);
                if (data.length === 0) {
                    categorySelect.innerHTML = options;
                    attributeValueSelect.innerHTML = options;
                    categorySelect.style.pointerEvents = 'none';
                    categorySelect.style.backgroundColor = '#eee';
                    return;
                }
                data.forEach((obj) => {
                    options += `<option value="${obj.catId}">${obj.catName}</option>`;
                });
                categorySelect.innerHTML = options;
                categorySelect.removeAttribute('style');
            });
        });
    }

    addCategorySelectListener() {
        let supplierSelect = document.querySelector('select[name="supplierFilter"]');
        let attributeSelect = document.querySelector('select[name="attributeFilter"]');
        let attributeValueSelect = document.querySelector('select[name="attributeValueFilter"]');
        let categorySelect = document.querySelector('select[name="categoryFilter"]');
        categorySelect.addEventListener('change', () => {
            let supplierId = supplierSelect.value;
            let categoryId = categorySelect.value;
            attributeSelect.setAttribute('data-fetch-path', `/attribute-mapping/getSupplierAttributesForSearch/?id=${supplierId}&categoryId=${categoryId}`);
            //fetch attributes
            this.fetcher.fetchData(`/attribute-mapping/getAttributesBySupplierId/?id=${supplierId}&categoryId=${categoryId}`, 'GET').then((data) => {
                let options = '<option value="-1">---</option>';
                data = JSON.parse(data);
                if (data.length === 0) {
                    attributeSelect.innerHTML = options;
                    attributeValueSelect.innerHTML = options;
                    attributeValueSelect.style.pointerEvents = 'none';
                    attributeValueSelect.style.backgroundColor = '#eee';
                    attributeSelect.style.pointerEvents = 'none';
                    attributeSelect.style.backgroundColor = '#eee';
                    return;
                }
                data.forEach((attribute) => {
                    options += `<option value="${attribute}">${attribute}</option>`;
                });
                attributeSelect.innerHTML = options;
                attributeSelect.removeAttribute('style');
            });
        });
    }

    addAttributeSelectListener() {
        let attributeSelect = document.querySelector('select[name="attributeFilter"]');
        let attributeValueSelect = document.querySelector('select[name="attributeValueFilter"]');

        attributeSelect.addEventListener('change', () => {
            let attribute = encodeURIComponent(attributeSelect.value);
            let supplierId = document.querySelector('select[name="supplierFilter"]').value;
            attributeValueSelect.setAttribute('data-fetch-path', `/attribute-mapping/getSupplierAttributeValuesForSearch/?id=${supplierId}&attribute=${attribute}`);
            this.fetcher.fetchData(`/attribute-mapping/getAttributeValuesByAttribute/?id=${supplierId}&attribute=${attribute}`, 'GET').then((data) => {
                let options = '<option value="-1">---</option>';
                data = JSON.parse(data);
                if (data.length === 0) {
                    attributeValueSelect.innerHTML = options;
                    attributeValueSelect.style.pointerEvents = 'none';
                    attributeValueSelect.style.backgroundColor = '#eee';
                    return;
                }
                data.forEach((attributeValue) => {
                    options += `<option value="${attributeValue}">${attributeValue}</option>`;
                });
                attributeValueSelect.innerHTML = options;
                attributeValueSelect.removeAttribute('style');
            });
        });
    }

    addLocalAttributeSelectListener() {
        let containers = document.querySelectorAll('.localAttributeWrapper');
        containers.forEach((container) => {
            this.addFetchValuesListeners(container);
        });
    }

    addAttributeEditListener() {
        let button = document.getElementById('addNewAttribute');
        button.addEventListener('click', () => {
            //open iframe
            let iframe = document.createElement('iframe');
            button.disabled = true;
            iframe.src = '/attribute/view/';
            let modal = document.getElementById('attributeIframeModal');
            iframe.addEventListener('load', () => {
                iframe.contentWindow.document.querySelector('.navbar-nav').style.display = 'none';
                iframe.contentWindow.document.querySelector('tfoot').style.display = 'none';
                iframe.contentWindow.document.querySelector('.navbar').style.display = 'none';
                iframe.contentWindow.document.querySelector('footer').style.display = 'none';
                iframe.contentWindow.document.querySelector('#modal').style.background = 'rgba(0, 0, 0, 0.7)';
                iframe.contentWindow.document.body.addEventListener('dataChanged', (e) => {
                    let data = JSON.parse(e.detail.data);
                    if (data.errors.length === 0) {
                        let modelData = (data.data);
                        this.closeIframeModal();
                        let select = document.getElementById('localAttribute');
                        //check if option already exists
                        let options = select.querySelectorAll('option');
                        let optionExists = false;
                        options.forEach((option) => {
                            if (option.value == modelData.id) {
                                optionExists = true;
                            }
                        });
                        if (!optionExists) {
                            let option = document.createElement('option');
                            option.value = modelData.id;
                            option.innerHTML = modelData.attributeName;
                            select.appendChild(option);
                        }
                        select.value = modelData.id;
                        select.dispatchEvent(new Event('change'));
                    }
                });
                modal.classList.add('show');
                button.disabled = false;
            });
            modal.appendChild(iframe);
        });
    }

    iframeModalStuff() {
        let closeBtn = document.getElementById('closeIframe');
        closeBtn.addEventListener('click', () => {
            this.closeIframeModal();
        });
    }

    closeIframeModal() {
        let modal = document.getElementById('attributeIframeModal');
        modal.classList.remove('show');
        modal.querySelector('iframe').remove();
    }

    initFilterAjaxSearch() {
       //init for supplier attribute
        let supplierAttributes = document.querySelector('select[name="attributeFilter"]');
        supplierAttributes.setAttribute('data-name-param', 'attribute');
        supplierAttributes.setAttribute('data-id-param', 'attribute');
        supplierAttributes.classList.add('ajaxSearch');

        let supplierAttributeValues = document.querySelector('select[name="attributeValueFilter"]');
        supplierAttributeValues.setAttribute('data-name-param', 'attributeValue');
        supplierAttributeValues.setAttribute('data-id-param', 'attributeValue');
        supplierAttributeValues.classList.add('ajaxSearch');

        // let attributeSelect = document.querySelector('select[name="localAttributesFilter"]');
        // attributeSelect.setAttribute('data-name-param', 'attributeName');
        // attributeSelect.setAttribute('data-id-param', 'id');
        // attributeSelect.setAttribute('data-fetch-path', '/attribute/tableHandler/');
        // attributeSelect.classList.add('ajaxSearch');
        //
        // let attributeValueSelect = document.querySelector('select[name="localAttributeValuesFilter"]');
        // attributeValueSelect.setAttribute('data-name-param', 'attributeValue');
        // attributeValueSelect.setAttribute('data-id-param', 'id');
        // attributeValueSelect.setAttribute('data-fetch-path', '/attribute-values/tableHandler/');
        // attributeValueSelect.classList.add('ajaxSearch');

        let categorySelect = document.querySelector('select[name="categoryFilter"]');
        categorySelect.setAttribute('data-name-param', 'name');
        categorySelect.setAttribute('data-id-param', 'id');
        categorySelect.setAttribute('data-fetch-path', '/category/tableHandler/');
        categorySelect.classList.add('ajaxSearch');
        let search = new TheSimplestAjaxSearch('ajaxSearch');
        search.init();
    }

    applyMappingButtonListener(){
        let applyNow = document.querySelectorAll('.applyMapping');
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
    }
    undoMappingButtonListener(){
        let undoRule = document.querySelectorAll('.undoMapping');
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

    addNewLocalAttributeListener(){
        let button = document.getElementById('addNewLocalAttribute');
        button.addEventListener('click', () => {
            let template = document.getElementById('localAttributesInputs');
            let clone = template.content.cloneNode(true);
            let wrapper = clone.querySelector('.localAttributeWrapper');
            new TheSimplestAjaxSearch(wrapper.querySelectorAll('.ajaxSearchForm'), false).init();
            this.addFetchValuesListeners(wrapper);
            this.addDeleteLocalAttributeButtonListener(wrapper.querySelector('.deleteLocalAttribute'));
            let container = document.getElementById('localAttributesContainer');
            container.appendChild(clone);
        });
    };

    addFetchValuesListeners(container){
        let select = container.querySelector('.attributeSelect')
        let valuesSelect = container.querySelector('.attributeValuesSelect');
        valuesSelect.dataset.filter = '{"attributeId": "' + select.value + '"}';
        select.addEventListener('change', () => {
            this.fetcher.fetchData('/attribute-values/fetchForAjaxSearch/?attributeId=' + select.value, 'GET').then((data) => {
                data = JSON.parse(data);
                valuesSelect.name = 'localAttributeValues[' + select.value + '][]';
                let options = '<option value="-1">---</option>';
                data.forEach((attributeValue) => {
                    options += `<option value="${attributeValue.id}">${attributeValue.value}</option>`;
                });
                valuesSelect.dataset.filter = '{"attributeId": "' + select.value + '"}';
                valuesSelect.innerHTML = options;
            });
        })
    };

    addDeleteLocalAttributeButtonListener(button) {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.target.parentElement.parentElement.remove();
        });
    }

    initDeleteButtonsListener(){
        let buttons = document.querySelectorAll('.deleteLocalAttribute');
        buttons.forEach((button) => {
            this.addDeleteLocalAttributeButtonListener(button);
        });
    }
}