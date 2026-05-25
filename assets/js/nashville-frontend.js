document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Lógica Tap-to-Verify para la tarjeta digital
    const memberCard = document.querySelector('.member-card__pulse');
    if (memberCard) {
        memberCard.addEventListener('click', async () => {
            const statusEl = document.querySelector('.verify-status');
            statusEl.textContent = 'Verificando...';
            
            try {
                const res = await fetch(`${nashvilleData.apiUrl}/verify-card`, {
                    method: 'POST',
                    headers: { 
                        'X-WP-Nonce': nashvilleData.nonce,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                
                if (data.verified) {
                    statusEl.innerHTML = `&#10003; Activa &middot; ${data.timestamp}`;
                } else {
                    statusEl.textContent = 'Error de verificación';
                }
            } catch (error) {
                statusEl.textContent = 'Error de conexión';
            }
        });
    }

    // 2. Lógica para reclamar ofertas (Pre-Claim)
    const claimButtons = document.querySelectorAll('.claim-btn[data-offer-id]');
    claimButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const offerId = btn.getAttribute('data-offer-id');
            const originalText = btn.textContent;
            
            btn.textContent = 'Generando código...';
            btn.disabled = true;

            try {
                const res = await fetch(`${nashvilleData.apiUrl}/claim-offer`, {
                    method: 'POST',
                    headers: { 
                        'X-WP-Nonce': nashvilleData.nonce,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ offer_id: offerId })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    // Cambiar el botón por el código dorado
                    btn.outerHTML = `<div class="claimed-code-box">
                                        <div class="code-label">TU CÓDIGO VIP:</div>
                                        <div class="code-value">${data.code}</div>
                                        ${data.notes ? `<div class="code-notes">${data.notes}</div>` : ''}
                                     </div>`;
                } else {
                    alert('Aviso: ' + data.message);
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Error de conexión al reclamar.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    });

});
