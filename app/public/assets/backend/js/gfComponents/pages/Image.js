import CrudPage from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Page/CrudPage.js";
import {dataTablesAssets} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/DataTable/dataTablesAssets.js";
import {dataTableSelectors} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/DataTable/dataTableSelectors.js";

export default class Image extends CrudPage {
    constructor() {
        super();
        this.dataTableOptions = {
            enableCheckboxes: true,
            shiftCheckboxModifier: true
        };
    }
}