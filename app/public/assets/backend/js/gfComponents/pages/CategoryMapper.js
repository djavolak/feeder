import BasePage from "../components/BasePage.js";

export default class CategoryMapper extends BasePage {
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
    }
}