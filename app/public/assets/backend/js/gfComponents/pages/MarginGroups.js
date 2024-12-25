import BasePage from "../components/BasePage.js";

export default class MarginGroups extends BasePage {
    constructor(crudTable) {
        super(crudTable);
        this.addAdditionalListeners();
        this.modalStyleConfig = {
            create: {
                modalWidth: '800px',
                modalHeight: '800px'
            },
            edit: {
                modalWidth: '800px',
                modalHeight: '800px'
            }
        }
    }

    addAdditionalListeners() {
        this.addRuleListeners();
    }

    addRuleListeners() {
        document.body.addEventListener(this.formReadyEventName, () => {
            this.addRuleButton = document.getElementById('addRule');
            this.existingRules = document.querySelectorAll('.deleteMargin');
            this.existingRules.forEach((rule) => {
                this.addDeleteMarginListener(rule);
            });

            this.addRuleButton.addEventListener('click', (e) => {
                e.preventDefault();
                let marginRulesContainer = document.getElementById('marginRulesContainer');
                let marginTemplate = document.getElementById('marginRuleTemplate');
                let templateContent = marginTemplate.content.cloneNode(true);
                let deleteButtonInNewMarginRule = templateContent.querySelector('.deleteMargin');
                marginRulesContainer.appendChild(templateContent);
                this.addDeleteMarginListener(deleteButtonInNewMarginRule);
            });
        });
    }

    addDeleteMarginListener(elem) {
        elem.addEventListener('click', () => {
            elem.parentElement.remove();
        });
    }
}