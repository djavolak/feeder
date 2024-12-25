import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/Page/CrudPage.js";
import {dataTablesAssets} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/DataTable/dataTablesAssets.js";
import {dataTableSelectors} from "https://skeletor.greenfriends.systems/dtables/1.x/0.1/src/DataTable/dataTableSelectors.js";

export default class Post extends CrudPage {
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
    }
}