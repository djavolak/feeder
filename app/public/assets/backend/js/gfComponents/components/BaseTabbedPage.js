import Helper from "../utils/Helper.js";
import Fetcher from "../utils/Fetcher.js";
import Response from "../utils/Response.js";
import FormValidator from "./FormValidator/FormValidator.js";

export default class BaseTabbedPage {
    constructor(props = null) {
        this.helper = new Helper();
        this.fetcher = new Fetcher();
        this.tabbedContentContainer = document.getElementById('tabbedContentContainer');
        this.tabs = document.querySelectorAll('.tab') ?? [];
        this.tabContent = document.getElementById('tabContent');
        this.formSubmittedEventName = 'formSubmitted';
        this.printErrorsInModal = props?.printErrorsInModal ?? false;
    }

    init() {
        this.selectTab(this.tabs[0]).then(() => {
            this.addTabListeners();
            this.addFormSubmittedEventListener();
        });
    }

    addValidation() {
        let form = this.helper.getForm();
        if(form) {
            let formValidator = new FormValidator({
                form: form,
                formFieldClassNames: 'formInput',
            });
            formValidator.init();
        }
    }

    addTabListeners() {
        this.tabs.forEach((tab) => {
           tab.addEventListener('click', async (e) => {
               if(tab.classList.contains('active')) {
                   return;
               }
               await this.selectTab(tab,e);
           });
        });
    }


    addSubmitFormListener() {
        let submitButton = document.getElementById('submitButton');
        let form = this.helper.getForm();
        if(submitButton) {
            submitButton.addEventListener('click', async (e) => {
                await this.doAction(submitButton,
                                    submitButton.getAttribute('data-action'),
                            'POST',
                                    e,
                                    new FormData(form));
            });
        }
    }

    addFormSubmittedEventListener() {
        this.tabbedContentContainer.addEventListener(this.formSubmittedEventName, (e) => {
            this.helper.removeErrorsFromPage();
            let response = new Response(e.detail.data);
            if(response.getErrors().length > 0) {
                if(response.getCSRFTokenInput()) {
                    this.helper.updateCSRFToken(response.getCSRFTokenInput());
                }
                this.helper.printErrors(response.getErrors(), this.printErrorsInModal);
                this.helper.scrollToTopOfWindow();
                return;
            }
            this.helper.printMessage(response.getStatus(), response.getMessage());
            this.helper.scrollToTopOfWindow();
        });
    }

    async doAction(initiator, action, method, e = null, formData = null) {
        this.helper.startLoader(this.tabContent, 'big');
        if(e) {
            e.preventDefault();
        }
        let data = await this.fetcher.fetchData(action, method, formData);
        this.helper.stopLoader();
        if(method === 'GET') {
            this.helper.populateContainer(this.tabContent, data);
            this.addValidation();
            this.addSubmitFormListener();
            if(initiator.getAttribute('data-form-ready-event-name')) {
                this.tabbedContentContainer.dispatchEvent(new CustomEvent(initiator.getAttribute('data-form-ready-event-name'), {
                    detail: {
                        data: data
                    }
                }));
            }
            return;
        }
        this.tabbedContentContainer.dispatchEvent(new CustomEvent(this.formSubmittedEventName, {
            detail: {
                data: data
            }
        }))
    }

    async selectTab(selectedTab,e) {
        this.tabs.forEach((tab) => {
            if(tab === selectedTab) {
                tab.classList.add('active');
                return;
            }
            tab.classList.remove('active');
        });
        if (this.tabs.length > 0 ) {
            await this.doAction(selectedTab, selectedTab.getAttribute('data-action'), 'GET', e);
        }
    }
}