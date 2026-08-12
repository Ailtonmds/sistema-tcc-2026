const form = document.getElementById("loginForm");

const usuario = document.getElementById("usuario");

const senha = document.getElementById("senha");

const mensagemErro = document.getElementById("mensagemErro");

const mostrarSenha = document.getElementById("mostrarSenha");


/* MOSTRAR / ESCONDER SENHA */

mostrarSenha.addEventListener("click", () => {

    if (senha.type === "password") {

        senha.type = "text";

        mostrarSenha.textContent = "🙈";

    } else {

        senha.type = "password";

        mostrarSenha.textContent = "👁";

    }

});


/* LOGIN */

form.addEventListener("submit", (event) => {

    event.preventDefault();

    mensagemErro.classList.remove("ativo");

    const usuarioDigitado = usuario.value.trim();

    const senhaDigitada = senha.value.trim();


    if (usuarioDigitado === "" || senhaDigitada === "") {

        mensagemErro.textContent =
            "Preencha todos os campos.";

        mensagemErro.classList.add("ativo");

        return;
    }


    /*
        TEMPORÁRIO

        Depois vamos substituir por:

        fetch("../backend/login.php", {
            method: "POST",
            ...
        })
    */

    if (
        usuarioDigitado === "admin" &&
        senhaDigitada === "123456"
    ) {

        window.location.href = "dashboard.html";

    } else {

        mensagemErro.textContent =
            "Usuário ou senha incorretos.";

        mensagemErro.classList.add("ativo");

    }

});