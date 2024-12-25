import Page from "./Page.js";

export default class Client extends Page {
    form = 'clientForm';

    constructor() {
        super();
        this.attachSubmitEvent();
        // this.attachSearchEvent();
        this.startTypeahead('#aprSearch');
    }

    startTypeahead(selector) {
        let config = {
            selector: selector,
            placeHolder: "Pretraga apr baze...",
            threshold: 3,
            searchEngine: function (query, record) {
                return record;
            },
            resultItem: {
                highlight: true,
                element: (item, data) => {
                    item.setAttribute("data-vat", data.value.vat);
                    item.setAttribute("data-mb", data.value.mb);
                    item.setAttribute("data-name", data.value.name);
                    item.setAttribute("data-address", data.value.address);
                    item.setAttribute("data-city", data.value.city);
                    item.setAttribute("data-country", data.value.country);
                    document.querySelector('.page-header').dispatchEvent(new Event('click'));
                },
            },
            events: {
                list: {
                    click: (event) => {
                        document.getElementsByName('invoiceName')[0].value = event.target.dataset.name;
                        document.getElementsByName('invoiceAddress')[0].value = event.target.dataset.address;
                        document.getElementsByName('invoiceCity')[0].value = event.target.dataset.city;
                        document.getElementsByName('invoiceCountry')[0].value = event.target.dataset.country;
                        document.getElementsByName('mb')[0].value = event.target.dataset.mb;
                        document.getElementsByName('pib')[0].value = event.target.dataset.vat;
                        autoCompleteJS.close();
                    }
                }
            },
            data: {
                src: async (query) => {
                    try {
                        const response = await fetch('/client/searchApr/?query=' + query, {
                            method: 'GET'
                        });
                        let body = await response.text();
                        if (response.status !== 200) {
                            console.log('Doslo je do greske prilikom komunikacije sa APR servisom.');
                            console.log(body);

                            return;
                        }

                        return JSON.parse(body);
                    } catch (error) {
                        return error;
                    }
                },
                // Data key to be filtered
                keys: ["name"]
            },
        };
        const autoCompleteJS = new autoComplete(config);
    }

    attachSearchEvent() {
        let aprSearch = document.getElementById('aprSearch');
        let that = this;
        let timer = 0;
        const waitTime = 1000;
        aprSearch.addEventListener('keydown', async function (e) {
            if (timer === 0) {
                timer = Date.now();
            }
        });

        aprSearch.addEventListener('keyup', async function (e) {
            clearTimeout(timer);
            timer = setTimeout(() => {
                that.search(e.target.value);
            }, waitTime);
        });
    }

    async search(query) {
        // this.showLoader();
        const response = await fetch('/client/searchApr/?query=' + query, {
            method: 'GET'
        });
        let body = await response.text();
        if (response.status !== 200) {
            console.log('There was an error submitting the form.');
            console.log(body);

            return;
        }
        const items = [];
        Object.values(JSON.parse(body)).forEach(function(item) {
            console.log(item);
            items.push(item);
        });
        // this.hideLoader();
        return items;
    }

    switchClientType() {
        let select = document.getElementsByClassName('clientType')[0];
        let personalIdLabel = document.getElementsByClassName('personalId')[0];
        let companyIdLabel = document.getElementsByClassName('companyId')[0];

        if (select.value == 1) {
            let vat = document.getElementsByName('pib')[0];
            vat.parentElement.hidden = false;
            this.addClass(personalIdLabel, 'none');
            this.removeClass(companyIdLabel, 'none');

            return;
        }
        if (select.value == 2) {
            let vat = document.getElementsByName('pib')[0];
            vat.parentElement.hidden = true;
            this.removeClass(personalIdLabel, 'none');
            this.addClass(companyIdLabel, 'none');
        }
    }
}