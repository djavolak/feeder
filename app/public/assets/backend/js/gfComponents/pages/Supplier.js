import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {dataTableEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/DataTable/dataTableEvents.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/pageEvents.js";

export default class Supplier extends CrudPage {
    constructor() {
        super();
        this.dataTableOptions = {
            enableCheckboxes: true,
            shiftCheckboxModifier: true
        };
        this.defaultSort = [
            {order: 'DESC', orderBy: 'created_at'},
            {order: 'DESC', orderBy: 'updated_at'}
        ];
        this.addAdditionalListeners();
        this.modalStyleConfig = {
            create: {
                modalWidth: '800px',
            },
            edit: {
                modalWidth: '800px',
            }
        }
    }

    addAdditionalListeners() {
        // this.crudTable.addEventListener(this.crudTable.tablePopulatedEventName, () => {
        this.eventEmitter.on(pageEvents.tablePopulated, (data) => {
            this.highlightSupplierIfInGet();
        });

    }

    // @TODO does not work
    highlightSupplierIfInGet() {
        let urlParams = new URLSearchParams(window.location.search);
        let supplierId = urlParams.get('supplierId');
        if(supplierId) {
            let foundRow = document.querySelector(`tr[data-id="${supplierId}"]`);
            if(foundRow) {
                foundRow.scrollIntoView();
                foundRow.classList.add('highlight');
            }
        }
    }

}