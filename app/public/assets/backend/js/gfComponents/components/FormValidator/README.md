# FormValidator

### Usage
_____
####JavaScript

```js
let form = document.getElementById('yourForm');
let formValidator = new FormValidator({
    form: form,
    formFieldClassNames: 'yourInputClassName',
    formScrollableContainer: document.getElementById('yourScrollableContainer')
});
formValidator.init();
```
####arguments
```form``` represents the form element inside your HTML. (required)

```formFieldClassName``` represents the class name of your inputs. (required)

```formScrollableContainer``` if the form is inside a modal or any other scrollable container we should pass 
it here. If an element is not provided the default value will be the ``window`` object. When a field is invalid the
the ```formScrollableContainer``` will be scrolled to the first invalid input.

___
####HTML
Configurating the validation is done through HTML data attributes.

Preferably one input should have one container.
```html
<div class="someContainer">
    <label for="someInput">Some Label</label>
    <input name="someInput" id="someInput" class="someClass"/>
</div>
```
Note that the input has the class ```someClass```. This class would be passed to the FormValidator constructor as ```formFieldClassNames```

All data attributes are optional except data pairs, which must co-exist.
For example, if a field has a data attribute for minimum length, it must have a minimum length message attribute as well.

Inputs that have the class name ```hidden``` will not be validated. This can be useful when you have inputs that are shown/hidden depending on some user action.
####Data attributes
`data-required` - `true`,`false`

`data-required-text` - Text to display if the input is not set but is required.

`data-select-empty-value` - Value which is considered "empty" for the `select` element, for example `-1`.

`data-validation-strategy` - `onlyLetters`, `uppercase`, `lowercase`, `email`, (can also pass custom regex as value).

if using regex, the regex that u input into the data-validation-strategy should omit the wrapping `/ /` for example the regexp `/^hello/` should be passed as `^hello`.

`data-validation-strategy-message` - Text to display if the strategy fails.

`data-max-len` - Max length for text fields.

`data-max-len-message` - Text to display if the value length of an input is `>` than `data-max-len`.

`data-min-len` - Min length for text fields.

`data-min-len-message ` - Text to display if the value length of an input is `<` than `data-min-len`.

`data-exact-len` - Exact length for text fields.

`data-exact-len-message` - Text to display if the value length of the input is not equal to `data-exact-len`.

`data-max-num` - Max value for number fields.

`data-max-num-message` - Text to display if the value of the input is `>` than `data-max-num`.

`data-min-num` - Min value for number fields.

`data-min-num-message` - Text to display if the value of the input is `<` than `data-min-num`.


####Validation order
Validations are executed in an order, which means only one validation can fail at a time.

This will be a feature in the future, when you will be able to choose to print all failed validations, or a single one.

Validation order is as follows:

Empty input if required

1. `Max len`

2. `Min len`

3. `Exact len`

4. `Max num`

5. `Min num`

6. `Validation strategy`


####Example
We want to validate an input which is a text field. The input can be empty (it is not required). The maximum length is 16 characters.
```html
<div class="someContainer">
    <label for="someInput">Some Label</label>
    <input data-max-len="16"
           data-max-len-message="The maximum number of characters is 16"
           name="someInput" id="someInput" class="someClass"/>
</div>
```

We want to validate an input which is of type number. It is a required field. The maximum number is 14, and the minimum is 6.
```html
<div class="someContainer">
    <label for="someInput">Some Label</label>
    <input data-required="true"
           data-required-message="This field is required"
           data-max-num="14"
           data-max-len-message="The maximum value must not exceed 14"
           data-min-num="6"
           data-min-num-message="The minimum value must not be below 6"
           type="number" name="someInput" id="someInput" class="someClass"/>
</div>
```

We want to validate an input that is required, can only contain letters, and must not be longer than 8 characters.
```html
<div class="someContainer">
    <label for="someInput">Some Label</label>
    <input data-required="true"
           data-required-message="This field is required"
           data-validation-strategy="onlyLetters"
           data-validation-strategy-message="Only letters are allowed"
           data-max-len="8"
           data-max-len-message="The maximum number of characters must not exceed 8"
           name="someInput" id="someInput" class="someClass"/>
</div>
```

