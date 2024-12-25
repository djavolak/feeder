import Block from "../Block.js";
import {blockSelectors} from "../blockSelectors.js";
import {entityListSelectors} from "./entityListSelectors.js";
import ValuesGenerator from "../../../valuesGenerator/ValuesGenerator.js";
import {modalSelectors} from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Modal/modalSelectors.js";
import BlockBuilder from "../../BlockBuilder.js";

export default class EntityListBlock extends Block {

    valuesGenerator = null;

    blockBuilder = new BlockBuilder(entityListSelectors.classes.entityListContainer);

    container;

    titleInput;

    entityTypeSelect;

    subEntityTypeSelect;

    viewTypeSelect;

    sortSelect;

    flagSelect;

    constructor(id, blockName, handlerEventEmitter, data, blockConfig = {}) {
        super(id, blockName, handlerEventEmitter, data);
        this.blockConfig = blockConfig;
        this.parseConfig();
        this.generateInputName();
        this.generateView();
        this.addListeners();
    }

    parseConfig() {
        this.entityTypes = this.blockConfig?.entityList?.entityTypes ?? null;
        this.viewTypes = this.blockConfig?.entityList?.viewTypes ?? null;
        this.sortOptions = this.blockConfig?.entityList?.sortOptions ?? null;
        this.flags = this.blockConfig?.entityList?.flags ?? null;
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][entityList]`;
    }

    getInputName() {
        return this.inputName;
    }

    generateView() {
        const defaultOption = {value: "-1", label: "---"};
        [
            this.container,
            this.titleInput,
            this.entityTypeSelect,
            this.subEntityTypeSelect,
            this.viewTypeSelect,
            this.sortSelect,
            this.flagSelect
        ] = this.blockBuilder
            .generateInput('Title', this.getTitleInputName())
            .generateSelect('Entity Type', this.parseEntityTypeSelectData(), this.getEntityTypeSelectName(), defaultOption)
            .generateSelect('Term', {}, this.getSubEntityTypeSelectName())
            .generateSelect('View Types', this.viewTypes, this.getViewTypeSelectName(), defaultOption)
            .generateSelect('Sort By', this.sortOptions, this.getSortSelectName(), defaultOption)
            .generateSelect('Flag', this.flags, this.getFlagSelectName(), defaultOption)
            .getSpread();
        this.blockBuilder = null; // Remove reference to allow GC to collect since we don't need the blockBuilder anymore
        this.subEntityTypeSelect.disabled = true;
    }

    parseEntityTypeSelectData() {
        const data = {};
        Object.keys(this.entityTypes).forEach((key) => {
            data[key] = this.entityTypes[key].title;
        })
        return data;
    }

    getTitleInputName() {
        return this.getInputName() + '[title]';
    }


    getEntityTypeSelectName() {
        return this.getInputName() + '[entityType]';
    }

    getSortSelectName() {
        return `${this.getInputName()}[orderBy]`;
    }



    getFlagSelectName() {
        return `${this.getInputName()}[flag]`;
    }

    getSubEntityTypeSelectName() {
        return `${this.getInputName()}[subEntityType]`;
    }



    removeValuesGenerator() {
        if(this.getValuesGenerator()) {
            this.getValuesGenerator().getContainer().remove();
        }
    }


    generateSearchEntity(entityData) {
        const data = {
            inputName: `${this.getInputName()}[${entityData.path}]`,
            label: `Search ${entityData.path}`,
            action: entityData.action,
            searchFieldValue: entityData.searchFieldValue,
            searchFieldViewValue: entityData.searchFieldViewValue,

        };
        const form = document.querySelector(`#${modalSelectors.ids.innerModal} form`) ?? null;
        this.valuesGenerator = new ValuesGenerator(null, form, data, false, true);
        this.valuesGenerator.init();
        this.getViewNode().appendChild(this.valuesGenerator.getContainer());
    }

    getValuesGenerator() {
        return this.valuesGenerator;
    }



    getViewTypeSelectName() {
        return this.getInputName() + '[viewType]';
    }


    addListeners() {
      this.addEntityTypeSelectChangeListener();
      this.addSubEntityTypeSelectListener();
    }

    addEntityTypeSelectChangeListener() {
        this.entityTypeSelect.addEventListener('change', () => {
            this.removeValuesGenerator();
            this.emptySubEntityTypeSelect();
            this.generateSubEntities();
        });
    }

    emptySubEntityTypeSelect() {
        this.subEntityTypeSelect.innerHTML = "";
    }

    generateSubEntities() {
        if(this.entityTypeSelect.value === "-1") {
            this.subEntityTypeSelect.disabled = true;
            return;
        }
        this.subEntityTypeSelect.disabled = false;
        const entity = this.entityTypes[parseInt(this.entityTypeSelect.value)];
        if (entity && entity.subEntities) {
            this.generateSubEntityOptions(entity.subEntities);
        }
    }

    generateSubEntityOptions(subEntities) {
        const fragment = document.createDocumentFragment();
        const defaultOption = document.createElement('option');
        defaultOption.value = "-1";
        defaultOption.innerText = '---';
        fragment.appendChild(defaultOption);
        Object.keys(subEntities).forEach((key) => {
            const option = document.createElement('option');
            option.value = key;
            option.innerText = subEntities[key].title;
            fragment.appendChild(option);
        });
        this.subEntityTypeSelect.appendChild(fragment);
    }

    addSubEntityTypeSelectListener() {
        this.subEntityTypeSelect.addEventListener('change', () => {
            this.removeValuesGenerator();
            const mainEntityId = parseInt(this.entityTypeSelect.value);
            const subEntityId = parseInt(this.subEntityTypeSelect.value);
            const targetEntity = this.entityTypes[mainEntityId].subEntities[subEntityId];
            if(targetEntity) {
                this.generateSearchEntity(targetEntity);
            }
        });
    }



    populateWithData() {
        if(this.data) {
            if(this.data.title && this.data.title.trim() !== '') {
                this.titleInput.value = this.data.title;
            }
            if(this.data.entityType || this.data.entityType === 0) {
                this.entityTypeSelect.value = this.data.entityType;
                this.entityTypeSelect.dispatchEvent(new Event('change'));
                if(this.data.subEntityType || this.data.subEntityType === 0) {
                    this.subEntityTypeSelect.value = this.data.subEntityType;
                    this.subEntityTypeSelect.dispatchEvent(new Event('change'));
                }
            }
            if(this.data.viewType) {
                this.viewTypeSelect.value = this.data.viewType;
            }
            if(this.data.orderBy) {
                this.sortSelect.value = this.data.orderBy;
            }
            if(this.data.flag) {
                this.flagSelect.value = this.data.flag;
            }
            if(this.data.entities && this.getValuesGenerator()) {
                this.data.entities.forEach((entity) => {
                    this.getValuesGenerator().generateValue(Object.keys(entity)[0], Object.values(entity)[0]);
                });
            }
        }
    }

    getViewNode() {
        return this.container;
    }
}