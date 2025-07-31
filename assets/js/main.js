function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    Toast.fire({
        icon: icon,
        title: title
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle delete impressora confirmation
    document.querySelectorAll('.delete-impressora').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const impressoraId = this.dataset.id;
            Swal.fire({
                title: 'Tem certeza?',
                text: "Voce nao podera reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'impressoras?delete=' + impressoraId;
                }
            });
        });
    });

    // Handle delete suprimento confirmation
    document.querySelectorAll('.delete-suprimento').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const suprimentoId = this.dataset.id;
            Swal.fire({
                title: 'Tem certeza?',
                text: "Voce nao podera reverter isso! Isso pode afetar o historico de trocas.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'estoque?delete=' + suprimentoId;
                }
            });
        });
    });

    // Real-time search for impressoras
    const searchImpressoraInput = document.getElementById('searchImpressora');
    if (searchImpressoraInput) {
        searchImpressoraInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = document.getElementById('impressorasTable');
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header row
                const td = tr[i].getElementsByTagName('td');
                let display = 'none';
                for (let j = 0; j < td.length - 1; j++) { // Exclude the last column (Actions)
                    if (td[j]) {
                        if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                            display = '';
                            break;
                        }
                    }
                }
                tr[i].style.display = display;
            }
        });
    }

    // Real-time search for suprimentos
    const searchSuprimentoInput = document.getElementById('searchSuprimento');
    if (searchSuprimentoInput) {
        searchSuprimentoInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = document.getElementById('suprimentosTable');
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip header row
                const td = tr[i].getElementsByTagName('td');
                let display = 'none';
                // Adjust the loop to include all relevant columns for search
                for (let j = 0; j < td.length - 2; j++) { // Exclude the last two columns (Qtd. Minima and Acoes)
                    if (td[j]) {
                        if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                            display = '';
                            break;
                        }
                    }
                }
                tr[i].style.display = display;
            }
        });
    }

    // Front-end validation for Add Impressora form
    const addImpressoraForm = document.getElementById('addImpressoraForm');
    if (addImpressoraForm) {
        addImpressoraForm.addEventListener('submit', function(e) {
            const codigo = document.getElementById('codigo').value.trim();
            const modelo = document.getElementById('modelo').value.trim();
            const localizacao = document.getElementById('localizacao').value.trim();

            if (codigo === '' || modelo === '' || localizacao === '') {
                e.preventDefault(); // Prevent form submission
                Swal.fire({
                    icon: 'error',
                    title: 'Campos Vazios',
                    text: 'Por favor, preencha todos os campos obrigatórios.'
                });
            }
        });
    }
});