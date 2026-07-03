function validarCPF() {

    let cpf = document.getElementById("cpf").value;

    cpf = cpf.replace(/\D/g, "");

    const erro = document.getElementById("erroCPF");

    if (cpf.length != 11) {
        erro.innerHTML = "CPF inválido.";
        return;
    }

    if (/^(\d)\1+$/.test(cpf)) {
        erro.innerHTML = "CPF inválido.";
        return;
    }

    let soma = 0;

    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }

    let resto = (soma * 10) % 11;

    if (resto == 10) resto = 0;

    if (resto != parseInt(cpf.charAt(9))) {
        erro.innerHTML = "CPF inválido.";
        return;
    }

    soma = 0;

    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }

    resto = (soma * 10) % 11;

    if (resto == 10) resto = 0;

    if (resto != parseInt(cpf.charAt(10))) {
        erro.innerHTML = "CPF inválido.";
        return;
    }

    erro.innerHTML = "";
}

function validarCNPJ() {

    let cnpj = document.getElementById("cnpj").value;

    cnpj = cnpj.replace(/\D/g, "");

    const erro = document.getElementById("erroCNPJ");

    if (cnpj.length != 14) {
        erro.innerHTML = "CNPJ inválido.";
        return;
    }

    if (/^(\d)\1+$/.test(cnpj)) {
        erro.innerHTML = "CNPJ inválido.";
        return;
    }

    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);

    let soma = 0;
    let pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }

    let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

    if (resultado != digitos.charAt(0)) {
        erro.innerHTML = "CNPJ inválido.";
        return;
    }

    tamanho++;
    numeros = cnpj.substring(0, tamanho);

    soma = 0;
    pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

    if (resultado != digitos.charAt(1)) {
        erro.innerHTML = "CNPJ inválido.";
        return;
    }

    erro.innerHTML = "";
}