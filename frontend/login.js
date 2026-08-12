const mostrarSenha = document.getElementById("mostrarSenha");
const eyeIcon = document.getElementById("eyeIcon");

mostrarSenha.addEventListener("click", () => {

    if (senha.type === "password") {

        senha.type = "text";

        eyeIcon.classList.remove("bi-eye");

        eyeIcon.classList.add("bi-eye-slash");

        mostrarSenha.setAttribute(
            "aria-label",
            "Ocultar senha"
        );

    } else {

        senha.type = "password";

        eyeIcon.classList.remove("bi-eye-slash");

        eyeIcon.classList.add("bi-eye");

        mostrarSenha.setAttribute(
            "aria-label",
            "Mostrar senha"
        );

    }

});