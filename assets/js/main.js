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
    // PWA install prompt (deferred)
    let deferredPrompt; const installBtnId='pwaInstallBtn';
    window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; const btn=document.getElementById(installBtnId); if(btn){ btn.classList.remove('hidden'); }});
    const maybeInstallBtn=document.getElementById(installBtnId);
    if(maybeInstallBtn){ maybeInstallBtn.addEventListener('click', async()=>{ if(!deferredPrompt) return; deferredPrompt.prompt(); const choice= await deferredPrompt.userChoice; if(choice.outcome==='accepted'){ showToast('success','App instalada'); } deferredPrompt=null; maybeInstallBtn.classList.add('hidden'); }); }

    // Display messages from PHP
    if (window.appMessage) {
        showToast(window.appMessage.type, window.appMessage.text);
        delete window.appMessage; // Clear the message after displaying
    }

    // Handle delete impressora confirmation
    document.querySelectorAll('.delete-impressora').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if(!form) return;
            Swal.fire({
                title: 'Excluir impressora?',
                text: 'Essa ação não pode ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Handle delete suprimento confirmation
    document.querySelectorAll('.delete-suprimento').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if(!form) return;
            Swal.fire({
                title: 'Excluir suprimento?',
                text: 'Essa ação não pode ser desfeita e pode afetar o histórico.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
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

    // Responsive table stacking for narrow screens
    function enhanceResponsiveTables(){
        if(window.innerWidth>768) return; // only mobile
        document.querySelectorAll('table.responsive-stack').forEach(tbl=>{
            if(tbl.dataset.enhanced) return; tbl.dataset.enhanced='1';
            const headers=[...tbl.querySelectorAll('thead th')].map(th=>th.textContent.trim());
            tbl.querySelectorAll('tbody tr').forEach(row=>{
                [...row.children].forEach((cell,i)=>{
                    if(headers[i]){
                        const span=document.createElement('span');
                        span.className='block text-xs font-semibold uppercase text-brand-500 dark:text-brand-400 mb-0.5';
                        span.textContent=headers[i];
                        const wrapper=document.createElement('div');
                        wrapper.className='sm:hidden';
                        while(cell.firstChild) wrapper.appendChild(cell.firstChild);
                        cell.appendChild(span); cell.appendChild(wrapper);
                        cell.classList.add('align-top','!py-3');
                    }
                });
            });
        });
    }
    enhanceResponsiveTables();
    window.addEventListener('resize',()=>enhanceResponsiveTables());
});
