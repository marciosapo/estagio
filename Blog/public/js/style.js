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