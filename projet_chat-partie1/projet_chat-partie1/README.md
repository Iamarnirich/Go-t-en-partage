# Projet Chat en Temps Réel en C

## Objectif

Ce projet consiste à créer une application client-serveur permettant à plusieurs utilisateurs de discuter en temps réel via le terminal. Chaque utilisateur peut choisir un pseudonyme, envoyer des messages, changer de salon, et se déconnecter proprement.

---

## Contenu du projet

- `serveur_chat.c` : code du serveur, qui gère plusieurs clients, les salons, les messages, etc.
- `client_chat.c` : code du client, permettant de se connecter au serveur et d'interagir.
- `README.md` : ce fichier explicatif.

---

## Compilation

Ouvrir un terminal dans le dossier contenant les fichiers, puis taper :

gcc serveur_chat.c -o serveur -lpthread
gcc client_chat.c -o client

---

## Lancement 

Démarrer le serveur :

./serveur

Démarrer un client ou plusieurs clients: 

./client

---

## Fonctionnement

Dès la connexion :
Le client entre un pseudo.

Il rejoint automatiquement le salon général (#general).

Il peut ensuite envoyer des messages à tous les utilisateurs du même salon

---

## Commandes disponibles côté client

SEND <message> : Envoie un message à tous les utilisateurs du salon
JOIN <salon>: Rejoint un salon (le crée s’il n’existe pas)
DISCONNECT ou EXIT: Se déconnecte proprement du serveur

Si l'utilisateur tape simplement un message sans SEND, le client le formate automatiquement avec SEND [pseudo] message.

