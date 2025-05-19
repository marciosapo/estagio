document.addEventListener("DOMContentLoaded", function() {
    clearMessage();
});

function clearMessage(){
    setTimeout(function() {
        const sucesso = document.getElementById('flash-sucesso');
        const erro = document.getElementById('flash-erro');
        
        if (sucesso) {
            sucesso.style.display = 'none';
        }
        
        if (erro) {
            erro.style.display = 'none';
        }
    }, 4000);
} 

let aCorrer = false;
function verificarToken() {
    if (aCorrer) return Promise.resolve();
    aCorrer = true;
    return fetch('/verificar_token_expirado.php')
        .then(response => response.json())
        .then(data => {
            if (data.apagar === 1) {
                return new Promise((resolve) => {
                    const logoutTimer = setTimeout(() => {
                        Swal.close();
                        fetch('/Blog/logout.php', { method: 'POST' })
                            .then(() => {
                                window.location.href = '/Blog/logout';
                            });
                    }, 600000);
                    Swal.fire({
                        title: 'A sua sessão expirou. Deseja renovar?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, renovar',
                        cancelButtonText: 'Não, sair',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        clearTimeout(logoutTimer);
                        if (result.isConfirmed) {
                            fetch('/renovar_token.php', { method: 'POST' })
                                .then(res => res.json())
                                .then(res => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Token renovado!',
                                        text: res.mensagem || 'Sessão renovada!',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });

                                    setTimeout(() => {
                                        location.reload();
                                    }, 3000);
                                });
                        } else {
                            fetch('/Blog/logout.php', { method: 'POST' })
                                .then(() => {
                                    window.location.href = '/Blog/logout';
                                });
                        }
                        resolve();
                    });
                });
            }
        })
        .finally(() => {
            aCorrer = false;
        });
}

function iniciarVerificacaoToken() {
    verificarToken().finally(() => {
        setTimeout(iniciarVerificacaoToken, 120000);
    });
}

iniciarVerificacaoToken();