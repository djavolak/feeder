export default class CategoryTree {
    constructor() {
        this.container = document.getElementById('categoryList');
        this.search = document.getElementById('filterCategories');
        this.categories = this.container.querySelectorAll('.category');
        this.activeCats = new Set();
        this.level1Containers = this.container.querySelectorAll(`.${this.getLevel1ClassName()}`);
    }


    init() {
        this.addCategorySearchListener();
        this.addSelectCategoryListeners();
    }

    addCategorySearchListener() {
        this.search.addEventListener('input', () => {
           if(this.search.value.trim().length === 0) {
                this.showAllCategories();
                return;
           }
           this.hideCategories(this.search.value.trim());
        });
    }

    showAllCategories() {
        this.getLevel1Containers().forEach((container) => {
           container.classList.remove('hide');
        });
    }

    hideCategories(search) {
        const show = new Set();
        this.categories.forEach((category) => {
            let parent = category.parentElement;
            if(category.parentElement.classList.contains(this.getLevel2ClassName())) {
                parent = category.parentElement.parentElement;
            }
            if(category.parentElement.classList.contains(this.getLevel3ClassName())) {
                parent = category.parentElement.parentElement.parentElement;
            }
            if(!category.innerText.match(new RegExp(search, 'i')) && !show.has(parent.getAttribute('data-id'))) {
                parent.classList.add('hide');
                return;
            }
            show.add(parent.getAttribute('data-id'));
            parent.classList.remove('hide');
        });
    }

    getLevel1Containers() {
        return this.level1Containers;
    }


    addSelectCategoryListeners() {
        this.categories.forEach((category) => {
            this.activateInitial(category);
            category.addEventListener('click', () => {
                this.handleCategoryClick(category);
            });
        });
    }

    activateInitial(category) {
        if(category.classList.contains(this.getActiveCategoryClassName())) {
            this.activeCats.add(category.getAttribute('data-id'));
        }
    }

    deselectAllCategories() {
        this.categories.forEach((category) => {
            category.classList.remove(`${this.getActiveCategoryClassName()}`);
            this.activeCats.delete(category.getAttribute('data-id'));
        });
    }

    handleCategoryClick(category) {
        let activate = true;
        if(category.classList.contains(this.getActiveCategoryClassName())) {
            activate = false;
        }
        let parent = category.parentElement;
        let level = parent.getAttribute(this.getLevelAttribute());
        switch(level) {
            case this.getLevel1ClassName():
                this.propagateLevel1(category, parent, activate);
                break;
            case this.getLevel2ClassName():
                this.propagateLevel2(category, parent, activate);
                break;
            case this.getLevel3ClassName():
                this.propagateLevel3(category, parent, activate);
                break;
        }
        this.removeInputs();
        this.activeCats.forEach((catId) => {
           this.generateInput(catId);
        });
    }

    propagateLevel1(category, parent, activate) {
        this.deselectAllCategories();
        if(activate) {
            category.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(category.getAttribute('data-id'));
            return;
        }
        category.classList.remove(this.getActiveCategoryClassName());
        const level2and3 = [...this.getLevel2(parent),...this.getLevel3(parent)];
        level2and3.forEach((child) => {
           child.classList.remove(this.getActiveCategoryClassName());
        });

    }

    propagateLevel2(category, parent, activate) {
        const firstLevel = category.parentElement.parentElement;
        const firstLevelCategory = firstLevel.querySelector('h2');
        if(!this.activeCats.has(firstLevel.getAttribute('data-id'))) {
            this.deselectAllCategories();
        }
        if(activate) {
            firstLevelCategory.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(firstLevelCategory.getAttribute('data-id'));
            category.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(category.getAttribute('data-id'));
            return;
        }
        category.classList.remove(this.getActiveCategoryClassName());
        this.activeCats.delete(category.getAttribute('data-id'));
        this.getLevel3(parent).forEach((level3) => {
            level3.classList.remove(this.getActiveCategoryClassName());
            this.activeCats.delete(level3.getAttribute('data-id'));
        });

    }

    propagateLevel3(category, parent, active) {
        const firstLevel = category.parentElement.parentElement.parentElement;
        const firstLevelCategory = firstLevel.querySelector('h2');

        const secondLevel = category.parentElement.parentElement;
        const secondLevelCategory = secondLevel.querySelector('h3');
        if(!this.activeCats.has(firstLevel.getAttribute('data-id'))) {
            this.deselectAllCategories();
        }
        if(active) {
            firstLevelCategory.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(firstLevelCategory.getAttribute('data-id'));
            secondLevelCategory.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(secondLevelCategory.getAttribute('data-id'));
            category.classList.add(this.getActiveCategoryClassName());
            this.activeCats.add(category.getAttribute('data-id'));
            return;
        }
        category.classList.remove(this.getActiveCategoryClassName());
        this.activeCats.delete(category.getAttribute('data-id'));

    }

    getLevel2(container) {
        return container.querySelectorAll('h3');
    }

    getLevel3(container) {
        return container.querySelectorAll('h4');
    }

    getInputName() {
        return 'categories[]';
    }

    getActiveCategoryClassName() {
        return 'active';
    }


    getLevel1ClassName() {
        return 'level1';
    }

    getLevel2ClassName() {
        return 'level2';
    }

    getLevel3ClassName() {
        return 'level3';
    }

    getLevelAttribute() {
        return 'data-level';
    }

    generateInput(value) {
        const input = document.createElement('input');
        input.name = this.getInputName();
        input.value = value;
        input.type = 'hidden';
        this.container.appendChild(input);
    }

    removeInputs() {
        this.container.querySelectorAll(`input[name="${this.getInputName()}"]`).forEach((input) => {
           input.remove();
        });
    }
}