document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".btn").forEach(button => {
        button.addEventListener("click", () => toggleAbonnement(button));
    });
});

function toggleAbonnement(button) {
    const abonnement = button.getAttribute("data-abonnement");
    const action = button.classList.contains("follow") ? "suivre" : "nePlusSuivre";
    console.log(abonnement, action);

    fetch(`../PHP/suivre_neplussuivre_abonne.php?action=${action}&abonnement=${abonnement}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Inverser l'action et mettre à jour l'interface
            if (action === "suivre") {
                button.innerText = "Ne plus suivre";
                button.classList.remove("follow");
                button.classList.add("unfollow");
            } else {
                button.innerText = "Suivre";
                button.classList.remove("unfollow");
                button.classList.add("follow");
            }
        } else {
            console.error("Erreur : ", data.message);
            alert("Erreur : impossible de mettre à jour l'abonnement.");
        }
    })
    .catch(error => alert("Erreur de connexion au serveur : " + error.message));
}
