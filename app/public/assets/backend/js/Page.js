export default class Page {

    submitButton = 'submitButton';
    form = '';

    constructor() {
        this.loader = document.getElementById('loader');
    }

    attachSubmitEvent() {
        let self = this;
        let element = document.getElementById(this.submitButton);
        if (element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                self.submitForm();
            });
        }
    }

    getSelectedOptions(sel) {
        let opts = [], opt;
        let len = sel.options.length;
        for (let i = 0; i < len; i++) {
            opt = sel.options[i];
            if (opt.selected) {
                opts.push(opt.value);
            }
        }

        return opts;
    }

    showLoader() {
        this.addClass(document.getElementsByClassName('contentWrapper')[0], 'none');
        this.removeClass(this.loader, 'none');
    }

    hideLoader() {
        this.addClass(this.loader, 'none');
        this.removeClass(document.getElementsByClassName('contentWrapper')[0], 'none');
    }

    async submitForm() {
        let formElement = document.getElementById(this.form);
        let data = new FormData(formElement);
        this.showLoader();

        const response = await fetch(formElement.action, {
            method: formElement.method,
            body: data
            // redirect: 'manual' // manual does not work, Location is never exposed !?!?!?!??!??!?!?!???!?!??!??!?!?!!?
        });
        let body = await response.text();
        if (response.status !== 200) {
            console.log('There was an error submitting the form.');
            console.log(body);

            return;
        }
        var el = document.createElement( 'html' );
        el.innerHTML = body;
        document.getElementsByClassName('contentWrapper')[0].innerHTML = el.getElementsByClassName('contentWrapper')[0].innerHTML;
        window.history.pushState({"html": body, "pageTitle": document.title},"", response.url);
        this.hideLoader();
        $('table').DataTable({
            // "order": [[ 5, "desc" ]],
            "pageLength": 25
        });
    }

    async fetchData(url, method, params = null) {
        const req = await fetch(url, {
            method: method,
            body: params
        });

        return JSON.parse(await req.text());
    }

    hasClass(el, className) {
        if (el.classList) {
            return el.classList.contains(className);
        }
        return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
    }

    addClass(el, className) {
        if (el.classList) {
            el.classList.add(className)
        } else if (!hasClass(el, className)) {
            el.className += " " + className;
        }
    }

    removeClass(el, className) {
        if (el.classList) {
            el.classList.remove(className)

            return;
        }
        if (hasClass(el, className)) {
            var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
            el.className = el.className.replace(reg, ' ');
        }
    }
}