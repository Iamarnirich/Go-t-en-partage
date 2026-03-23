document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".btn").forEach(button => {
        button.addEventListener("click", () => toggleFollow(button));
    });
});
function toggleFollow(button) {
    const abonnement = button.getAttribute('data-abonnement');
    const action="nePlusSuivre";

    fetch(`../PHP/neplussuivre_abonnement.php?action=${action}&abonnement=${abonnement}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Réponse du serveur :", data);
        if (data.success) {
            const parentElement = button.closest(".conteneurLigne"); // Utilise la classe de conteneur appropriée
            if (parentElement) {
                parentElement.remove();
            }
        } else {
            alert("Erreur : impossible de ne plus suivre. Détails : " + (data.message || "Action non réussie."));
        }
    })
    .catch(error => console.error("Erreur :" + error.message));
}

