const form = document.querySelector("form");

// Pobieramy inputy
const nameInput = form.querySelector('input[name="name"]');
const surnameInput = form.querySelector('input[name="surname"]');
const emailInput = form.querySelector('input[name="email"]');
const passwordInput = form.querySelector('input[name="password"]');
const confirmedPasswordInput = form.querySelector('input[name="confirmedPassword"]');

// Słownik timerów dla debounce
const timers = {}; 

// --- 1. FUNKCJE WALIDUJĄCE (Zwracają true/false i ustawiają komunikat) ---

function validateEmail(input) {
    // Prosty regex email
    const isValid = /\S+@\S+\.\S+/.test(input.value);
    toggleError(input, isValid, "Podaj poprawny adres email.");
    return isValid;
}

function validateName(input) {
    // Regex: Litery (polskie też), myślnik, spacja. Minimum 2 znaki.
    // To chroni przed XSS (nie pozwala na < > / itd.)
    const regex = /^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð \-]{2,}$/;
    const isValid = regex.test(input.value);
    toggleError(input, isValid, "Imię/Nazwisko może zawierać tylko litery (min. 2).");
    return isValid;
}

function validatePassword(input) {
    const isValid = input.value.length >= 6;
    toggleError(input, isValid, "Hasło musi mieć co najmniej 6 znaków.");
    return isValid;
}

function validateConfirmedPassword(input) {
    const isValid = input.value === passwordInput.value && input.value !== '';
    toggleError(input, isValid, "Hasła muszą być identyczne.");
    return isValid;
}

// --- 2. LOGIKA UI (Pokazywanie/Ukrywanie błędów) ---

function toggleError(input, isValid, message) {
    const errorText = input.parentElement.querySelector('.error-text');
    
    if (!isValid) {
        input.classList.add('no-valid');
        if (errorText) {
            errorText.textContent = message;
            errorText.classList.add('active');
        }
    } else {
        input.classList.remove('no-valid');
        if (errorText) {
            errorText.textContent = "";
            errorText.classList.remove('active');
        }
    }
}

// --- 3. OBSŁUGA DEBOUNCE (Opóźnienie podczas pisania) ---

function debounceValidation(input, validatorFn) {
    // Czyścimy poprzedni timer dla tego konkretnego inputa
    clearTimeout(timers[input.name]);

    // Ustawiamy nowy timer
    timers[input.name] = setTimeout(() => {
        validatorFn(input);
    }, 1000); // 1 sekunda opóźnienia
}

// Podpięcie zdarzeń 'keyup' (dla wygody użytkownika)
nameInput.addEventListener('keyup', () => debounceValidation(nameInput, validateName));
surnameInput.addEventListener('keyup', () => debounceValidation(surnameInput, validateName));
emailInput.addEventListener('keyup', () => debounceValidation(emailInput, validateEmail));
passwordInput.addEventListener('keyup', () => {
    debounceValidation(passwordInput, validatePassword);
    // Jeśli zmieniamy hasło, warto od razu sprawdzić też potwierdzenie, jeśli jest wypełnione
    if (confirmedPasswordInput.value) debounceValidation(confirmedPasswordInput, validateConfirmedPassword);
});
confirmedPasswordInput.addEventListener('keyup', () => debounceValidation(confirmedPasswordInput, validateConfirmedPassword));


// --- 4. GŁÓWNA WALIDACJA PRZY WYSYŁCE (SUBMIT) ---

form.addEventListener('submit', function(e) {
    // Czyścimy timery (żeby debounce nie nadpisał nam wyniku po chwili)
    Object.values(timers).forEach(timer => clearTimeout(timer));

    // Wykonujemy "twardą" walidację wszystkich pól natychmiast
    const isNameValid = validateName(nameInput);
    const isSurnameValid = validateName(surnameInput);
    const isEmailValid = validateEmail(emailInput);
    const isPasswordValid = validatePassword(passwordInput);
    const isConfirmValid = validateConfirmedPassword(confirmedPasswordInput);

    // Jeśli którekolwiek pole jest błędne -> BLOKUJEMY WYSYŁKĘ
    if (!isNameValid || !isSurnameValid || !isEmailValid || !isPasswordValid || !isConfirmValid) {
        e.preventDefault(); // Zatrzymaj wysyłanie formularza
        console.log("Formularz zawiera błędy, wysyłka zablokowana.");
    } else {
        // Wszystko OK, formularz poleci do PHP
        console.log("Formularz poprawny, wysyłanie...");
    }
});