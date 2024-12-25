import Page from "./Page.js";

export default class Invoice extends Page {

    form = 'invoiceForm';

    constructor() {
        super();
        this.attachSubmitEvent();
        let self = this;
        document.getElementsByClassName('selectedServices')[0].addEventListener("change",function(e) {
            if (e.target && e.target.matches(".qty")) {
                self.calculateAmount();
            }
        });
        document.getElementsByClassName('selectedServices')[0].addEventListener("click",function(e) {
            if (e.target && e.target.matches(".removeService")) {
                e.target.parentNode.remove();
                self.calculateAmount();
            }
        });
        document.getElementById('invoiceStep1').addEventListener("click",function(e) {
            self.addClass(document.getElementById('step1'), 'none');
            self.removeClass(document.getElementById('step2'), 'none');
        });
    }

    loadSelectedServices() {
        let self = this;

        let selectServices = document.getElementsByClassName('selectServices')[0];
        let selectedServices = document.getElementsByClassName('selectedServices')[0];
        selectServices.addEventListener('change', async function () {
            this.disabled = true;
            let value = this.value;
            let url = "/service/getService/" + self.getSelectedOptions(this).join(',') + "/";
            self.fetchData(url, 'GET').then((services) => {
                self.createElementsForSelectedService(selectedServices, services);
                this.disabled = false;
            });
            [...this.children].forEach(function (option) {
                if (option.value === value) {
                    option.remove();
                    return;
                }
            });
            this.value = 0;
        });
    }

    fillClientInfoForInvoice() {
        let self = this;
        let invoiceClient = document.getElementsByClassName('invoiceClient')[0];
        if (invoiceClient) {
            invoiceClient.addEventListener('change', function() {
                let clientId = invoiceClient.options[invoiceClient.selectedIndex].value;
                let url = "/client/getClient/" + clientId + "/";
                self.fetchData(url, 'GET').then((client) => {
                    document.getElementsByName('clientName')[0].value = client.clientName;
                    document.getElementsByName('clientAddress')[0].value = client.clientAddress;
                    document.getElementsByName('clientCity')[0].value = client.clientCity;
                    document.getElementsByName('clientCountry')[0].value = client.clientCountry;
                    document.getElementsByName('clientVatId')[0].value = client.clientId;
                    document.getElementsByName('clientVat')[0].value = client.clientVat;
                });
            });
        }
    }

    createElementsForSelectedService(selectedServices, services) {
        let self = this;
        let amount = 0;
        services.forEach((service) => {
            let entry = document.createElement('tr');
            entry.dataset.serviceId = service.serviceId;
            entry.dataset.price = service.price;
            entry.dataset.qty = 1;
            entry.className = 'selectedService';

            let input = document.createElement('input');
            input.type = 'text';
            input.name = 'selectedServices[name][]';
            input.value = service.name;
            let td = document.createElement('td');
            td.appendChild(input);
            entry.appendChild(td);

            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selectedServices[serviceId][]';
            input.value = service.serviceId;
            td = document.createElement('td');
            td.appendChild(input);
            input = document.createElement('input');
            input.placeholder = 'qty';
            input.className = 'form-control inputDesign qty';
            input.type = 'number';
            input.name = 'selectedServices[qty][]';
            input.value = 1;
            input.addEventListener('change', function() {
                self.calculateAmount();
            });
            td.appendChild(input)
            input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'm.u.';
            input.name = 'selectedServices[measureUnit][]';
            input.className = 'form-control inputDesign';
            td.appendChild(input)
            entry.appendChild(td);

            input = document.createElement('input');
            input.type = 'text';
            input.name = 'selectedServices[price][]';
            input.value = service.price;
            input.className = 'form-control inputDesign';
            td = document.createElement('td');
            td.appendChild(input);
            entry.appendChild(td);
            td = document.createElement('td');
            td.innerHTML = '<select name="selectedServices[vat][]">\n' +
                '<option value="0">0</option>\n' +
                '<option value="8">8</option>\n' +
                '<option value="10">10</option>\n' +
                '<option value="18">18</option>\n' +
                '</select>';
            entry.appendChild(td);

            input = document.createElement('input');
            input.type = 'text';
            input.readonly = 'readonly';
            input.name = 'selectedServices[rowTotal][]';
            input.className = 'form-control inputDesign';
            td = document.createElement('td');
            td.appendChild(input)
            entry.appendChild(td);

            input = document.createElement('span');
            input.innerHTML = '<span class="removeService">&nbsp; X</span>';
            entry.appendChild(document.createElement('td').appendChild(input));
            selectedServices.appendChild(entry);

            self.calculateAmount();
            selectedServices.getElementsByClassName('removeService')[0].addEventListener('click', function() {
                this.parentNode.remove();
            });
        });

    }

    calculateAmount() {
        let selectedServices = [...document.getElementsByClassName('selectedService')];
        let amount = 0;
        selectedServices.forEach((listItem) => {
            listItem.dataset.qty = listItem.getElementsByClassName('qty')[0].value;
            amount += Number(listItem.dataset.price * listItem.dataset.qty);
        });
        document.getElementsByName('amount')[0].value = amount;
    }
}