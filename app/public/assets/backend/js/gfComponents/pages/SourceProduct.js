import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {dataTableEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/DataTable/dataTableEvents.js";
import {pageEvents} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/pageEvents.js";

export default class SourceProduct extends CrudPage {
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
        this.modalStyleConfig = {
            create: {
                modalWidth: '800px',
            },
            edit: {
                modalWidth: '800px',
            }
        }
    }

}