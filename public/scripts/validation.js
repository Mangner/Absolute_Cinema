const form = document.getSelector("form");
const emailInput = form.querySelector('input[name="email"]');
const confirmedPasswordInput = form.querySelector('input[name="confirmedPassowrd"]');



function isEmail(email) {
    return /\S+@\S+\.\S+/.test(email);
}


function arePasswordsSame(password, confirmedPassowrd) {
    return password === confirmedPassowrd;
}


function markValidation(element, condition) {
    !condition
        ? element.classlist.add('no-valid')
        : element.classlist.remove('no-valid');
}


function validateEmail() {
    setTimeout(function () {
        markValidation(emailInput, isEmail(emailInput.value));
    }, 1000);
}


function validatePassword() {
    setTimeout(function () {
        const condition = arePasswordsSame(
            confirmedPasswordInput.previousElementSibling.value,
            confirmedPassowrdInput.value
        );
        markValidation(confirmedPasswordInput, condition);
    }, 1000);
}