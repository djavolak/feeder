import Helper from "../utils/Helper.js";
import Fetcher from "../utils/Fetcher.js";
import Response from "../utils/Response.js";
import FormValidator from "./FormValidator/FormValidator.js";

export default class BasePage {
    constructor(crudTable) {
        this.helper = new Helper();
        this.fetcher = new Fetcher();
        this.crudTable = crudTable;
        this.formReadyEventName = 'formReady';
        this.dataChangedEventName = 'dataChanged';
        this.selectedRowIds = [];
        this.addFormReadyListeners();
        this.addDataChangedEventListener();
        this.modalStyleConfig = {
            create: {
                modalWidth: '600px',
                modalHeight: '600px'
            },
            edit: {
                modalWidth: '600px',
                modalHeight: '600px'
            }
        }
        this.addEditBulkListener();
        this.addSelectAllRowsListener();
        this.tablePopulatedEventListener();
        void this.openModalIfIdInGet();
    }

    tablePopulatedEventListener() {
        this.crudTable.crudTable.addEventListener(this.crudTable.tablePopulatedEventName, () => {
            this.selectedRowIds = [];
            document.querySelectorAll('.selectAllRows').forEach((button) => {
               button.checked = false;
            });
        });
    }


    addSelectedRow(id) {
        this.selectedRowIds.push(id);
    }

    removeFromSelectedRows(id) {
        this.selectedRowIds = this.selectedRowIds.filter((value) => {
            return value !== id;
        });
    }

    //@todo merge this in doAction to avoid duplicate code
    async openModalIfIdInGet(id = null) {
        let aPath = this.crudTable.crudTable.getAttribute('data-admin-path');
        if(aPath !== null && aPath.trim() === '') {
            aPath = null;
        }
        let url = new URL(window.location);
        if(id === null) {
            id = url.searchParams.get('id');
        }
        if (id) {
            this.helper.openModal(this.modalStyleConfig.edit.modalWidth, this.modalStyleConfig.edit.modalHeight);
            this.helper.startLoader(this.helper.getInnerModal(), 'big');
            let fetchUrl = aPath ?? '/';
            fetchUrl = fetchUrl + `${this.helper.getEntityName()}/form/${id}/`;
            let data = await this.fetcher.fetchData(fetchUrl, 'GET');
            this.helper.stopLoader();
            this.helper.populateModal(data);
            document.body.dispatchEvent(new CustomEvent(this.formReadyEventName, {
                detail: {
                    data: data
                }
            }))
        }
    }

    registerSelectBulkActionListener() {
        let firstSelect = document.querySelectorAll('.bulkAction')[0];
        let secondSelect = document.querySelectorAll('.bulkAction')[1];
        let bulkApplyButtons =  document.querySelectorAll('.bulkApplyButton');

        if(firstSelect && secondSelect) {
            firstSelect.addEventListener('change', () => {
                secondSelect.value = firstSelect.value;
                this.setBulkApplyButtonDataAttribute(bulkApplyButtons, firstSelect.value);
            });

            secondSelect.addEventListener('change', () => {
                firstSelect.value = secondSelect.value;
                this.setBulkApplyButtonDataAttribute(bulkApplyButtons, secondSelect.value);
            });
        }
    }

    setBulkApplyButtonDataAttribute(bulkApplyButtons, value) {
        bulkApplyButtons.forEach((button) => {
            button.setAttribute('data-bulk-action', value);
        });
    }




    addEditBulkListener() {
        this.registerSelectBulkActionListener();
        document.querySelectorAll('.bulkApplyButton').forEach((button) => {
            button.addEventListener('click', async (e) => {
                if(this.selectedRowIds.length > 0) {
                    let formData = new FormData();
                    formData.append('ids', this.selectedRowIds.toString());
                    let names = [];
                    this.selectedRowIds.forEach((rowId) => {
                        names.push(document.querySelector(`.selectRow[data-id="${rowId}"]`).getAttribute('data-name'));
                    });
                    formData.append('names', names.toString());
                    let action;
                    let event;
                    let forceModal = false;
                    switch(document.querySelector('.bulkAction').value) {
                        case 'edit':
                            action = this.crudTable.bulkEditAction;
                            event = this.formReadyEventName;
                            forceModal = true;
                            break;
                        case 'sync':
                            action = this.crudTable.bulkSyncAction;
                            event = this.dataChangedEventName;
                            break;
                        case 'delete':
                            action = this.crudTable.bulkDeleteAction;
                            event = this.dataChangedEventName;
                            break;
                        default:
                            action = '-1'
                            break;
                    }
                    if(action === '-1') {
                        return;
                    }
                    await this.doAction(e,
                        button,
                        'large',
                        action,
                        'POST',
                        event,
                        formData, '1600px', '1000px', forceModal);
                }
            });
        });
    }

    addSelectAllRowsListener() {
        let selectAllButtons = document.querySelectorAll('.selectAllRows');
        selectAllButtons.forEach((button, index) => {
            button.addEventListener('change', () => {
                if(button.checked) {
                    if(index === 0) {
                        selectAllButtons[1].checked = true;
                    } else {
                        selectAllButtons[0].checked = true;
                    }
                    this.selectAllRows();
                    return;
                }
                if(index === 0) {
                    selectAllButtons[1].checked = false;
                } else {
                    selectAllButtons[0].checked = false;
                }
                this.deSelectAllRows();
            });
        });
    }

    selectAllRows() {
        this.selectedRowIds = [];
        document.querySelectorAll('.selectRow').forEach((button) => {
            this.selectedRowIds.push(parseInt(button.getAttribute('data-id')));
            button.checked = true;
        });
    }

    deSelectAllRows() {
        this.selectedRowIds = [];
        document.querySelectorAll('.selectRow').forEach((button) => {
            button.checked = false;
        });
    }


    addCreateButtonListener() {
        let createNew = document.getElementById('createNew');
        if(createNew) {
            createNew.addEventListener('click', async (e) => {
                let modalWidth = this.modalStyleConfig.create.modalWidth;
                let modalHeight = this.modalStyleConfig.create.modalHeight;
                await this.doAction(e,
                    createNew, 'big',
                    createNew.getAttribute('data-action'),
                    'GET', this.formReadyEventName,
                    null,
                    modalWidth, modalHeight);
            });
        }
    }


    addEditButtonListeners() {
        let modalWidth = this.modalStyleConfig.edit.modalWidth;
        let modalHeight = this.modalStyleConfig.edit.modalHeight;
        let editButtons = document.querySelectorAll('.editEntity');
        if(editButtons.length > 0) {
            editButtons.forEach((entity) => {
                entity.addEventListener('click', async (e) => {
                    await this.doAction(e,
                        entity, 'big',
                        entity.getAttribute('data-action'),
                        'GET', this.formReadyEventName,
                        null,
                        modalWidth, modalHeight);
                });
            });
        }
    }

    addDeleteButtonListeners() {
        let deleteButtons =  document.querySelectorAll('.deleteEntity');
        if(deleteButtons.length > 0) {
            deleteButtons.forEach((entity) => {
                entity.addEventListener('click', async (e) => {
                    await this.doAction(e,
                        entity, 'small',
                        entity.getAttribute('data-action'),
                        'POST', this.dataChangedEventName);
                });
            });
        }
    }

    addCopyAsDraftListeners() {
        let copyAsDraftButtons =  document.querySelectorAll('.copyAsDraft');
        if(copyAsDraftButtons.length > 0) {
            copyAsDraftButtons.forEach((entity) => {
                entity.addEventListener('click', async (e) => {
                    await this.doAction(e,
                        entity, 'small',
                        entity.getAttribute('data-action'),
                        'POST', this.dataChangedEventName);
                });
            });
        }
    }

    addCheckRowListeners() {
        let selectRowButtons = document.querySelectorAll('.selectRow');
        if(selectRowButtons.length > 0) {
            selectRowButtons.forEach((button) => {
               button.addEventListener('change', () => {
                   let rowId = parseInt(button.getAttribute('data-id'));
                  if(button.checked) {
                      this.addSelectedRow(rowId);
                      return;
                  }
                  this.removeFromSelectedRows(rowId);
               });
            });
        }
    }

    addFormReadyListeners() {
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addValidation();
            this.addSubmitFormListener();
        });
    }

    addValidation() {
        let form = this.helper.getModal().querySelector('form');
        let formValidator = new FormValidator({
            form: form,
            formFieldClassNames: 'formInput',
            formScrollableContainer: this.helper.getInnerModal()
        });
        formValidator.init();
    }

    addSubmitFormListener() {
        let submitButton = this.helper.getModal().querySelector('#submitButton');
        let form = this.helper.getModal().querySelector('form');
        if(submitButton) {
            submitButton.addEventListener('click', async (e) => {
                if(form.disableEnterSubmit) {
                    e.preventDefault();
                    return false;
                }
                await this.doAction(e,
                    submitButton,
                    'big',
                    submitButton.getAttribute('data-action'),
                    'POST',
                    this.dataChangedEventName, new FormData(form));
            });
        }
    }

    addDataChangedEventListener() {
        document.body.addEventListener(this.dataChangedEventName,(e) => {
            let response = new Response(e.detail.data);
            if(response.getErrors().length > 0 ) {
                if(response.getCSRFTokenInput()) {
                    this.helper.updateCSRFToken(response.getCSRFTokenInput());
                }
                this.helper.printErrors(response.getErrors());
                this.helper.scrollToTopOfModal();
                return;
            }
            if(response.getGeneralErrors().length > 0 ) {
                this.helper.printErrors(response.getGeneralErrors(), false);
            }
            this.helper.closeModal();
            if(response.getMessage() !== '') {
                this.helper.printMessage(response.getStatus(), response.getMessage());
            }
            this.crudTable.setupTable();
        });
    }

    isGetAndModalContentContainerExists(method) {
        return this.helper.getInnerModal() && method === 'GET';
    }


    async doAction(e, initiator, loaderSize,
                   action, method, eventName , formData = null,
                   modalWidth = '600px', modalHeight = '600px', forceModal = false) {
        e.preventDefault();
        if(this.isGetAndModalContentContainerExists(method) || forceModal) {
            this.helper.openModal(modalWidth, modalHeight);
            this.helper.startLoader(this.helper.getInnerModal(), loaderSize);
        } else {
            this.helper.removeErrorMessages();
            this.helper.startLoaderInElementParent(initiator, loaderSize);
        }
        let data = await this.fetcher.fetchData(action, method, formData);
        this.helper.stopLoader();
        if(this.isGetAndModalContentContainerExists(method) || forceModal) {
            this.helper.populateModal(data);
        } else {
            this.helper.makeElementVisibleAfterLoader(initiator);
        }
        document.body.dispatchEvent(new CustomEvent(eventName, {
            detail: {
                data: data
            }
        }))
    }
}