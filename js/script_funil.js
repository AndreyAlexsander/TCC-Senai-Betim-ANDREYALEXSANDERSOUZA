let cardArrastado = null;
let produtosFunil = [];

window.addEventListener('DOMContentLoaded', async () => {
    if (!(await requireLogin())) return;
    document.getElementById('buscaFunil').addEventListener('input', debounce(carregarFunil));
    document.getElementById('filtroStatus').addEventListener('change', carregarFunil);
    carregarFunil();
});

async function carregarFunil() {
    const filtros = new URLSearchParams({
        busca: document.getElementById('buscaFunil').value,
        status: document.getElementById('filtroStatus').value
    });

    const resposta = await fetch(`php/funil_produtos.php?${filtros.toString()}`);
    produtosFunil = await resposta.json();
    renderizarKanban();
}

function renderizarKanban() {
    const kanban = document.getElementById('kanban');
    kanban.innerHTML = STATUS_ORDER.map(status => {
        const itens = produtosFunil.filter(produto => produto.status === status);
        return `
            <article class="kanban-column">
                <div class="kanban-title"><span>${statusLabel(status)}</span><strong>${itens.length}</strong></div>
                <div class="kanban-list" data-status="${status}">
                    ${itens.length ? itens.map(cardProduto).join('') : '<div class="empty-state">Sem produtos</div>'}
                </div>
            </article>
        `;
    }).join('');

    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('dragstart', () => {
            cardArrastado = card;
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            cardArrastado = null;
        });
    });

    document.querySelectorAll('.kanban-list').forEach(list => {
        list.addEventListener('dragover', event => event.preventDefault());
        list.addEventListener('dragenter', () => list.classList.add('drag-over'));
        list.addEventListener('dragleave', () => list.classList.remove('drag-over'));
        list.addEventListener('drop', moverProduto);
    });
}

function cardProduto(produto) {
    return `
        <article class="kanban-card" draggable="true" data-id="${Number(produto.id)}">
            <h3>${escapeHtml(produto.nome)}</h3>
            <div class="meta-line">
                <span>${escapeHtml(produto.categoria)}</span>
                <span>${escapeHtml(produto.responsavel || 'Sem responsável')}</span>
            </div>
            <div class="meta-line">
                <span>Prioridade: ${escapeHtml(produto.prioridade || 'media')}</span>
            </div>
        </article>
    `;
}

async function moverProduto(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    if (!cardArrastado) return;

    const corpo = new URLSearchParams({
        id: cardArrastado.dataset.id,
        status: event.currentTarget.dataset.status
    });

    const resposta = await fetch('php/atualizar_status.php', { method: 'POST', body: corpo });
    const dados = await resposta.json();

    if (dados.sucesso) {
        showToast('Status atualizado.');
        carregarFunil();
    } else {
        alert(dados.mensagem || 'Erro ao atualizar.');
    }
}
