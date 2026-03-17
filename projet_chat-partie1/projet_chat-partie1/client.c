
#include <stdio.h>              
#include <stdlib.h>             
#include <string.h>             
#include <unistd.h>             
#include <arpa/inet.h>          
#include <sys/select.h>         

#define PORT 8080               // Port du serveur
#define BUFFER_SIZE 1024        // Taille maximale des buffers utilisés

int main() {
    int sock;                   // Socket du client
    struct sockaddr_in serv_addr; // Structure contenant l'adresse du serveur
    char buffer[BUFFER_SIZE];   // Buffer pour recevoir ou envoyer des messages
    char pseudo[32];            // Pseudo de l'utilisateur
    char send_cmd[BUFFER_SIZE];


    // Création de la socket (AF_INET = IPv4, SOCK_STREAM = TCP)
    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock == -1) {
        perror("socket");
        exit(1); // Quitte le programme si la socket échoue
    }

    // Configuration de l'adresse du serveur
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(PORT); // Port à utiliser
    serv_addr.sin_addr.s_addr = inet_addr("127.0.0.1"); // Adresse locale (localhost)

    // Connexion au serveur
    if (connect(sock, (struct sockaddr *)&serv_addr, sizeof(serv_addr)) < 0) {
        perror("connect");
        exit(1);
    }

    // Saisie du pseudo par l'utilisateur
    printf("Entrez votre pseudo : ");
    fgets(pseudo, sizeof(pseudo), stdin);
    pseudo[strcspn(pseudo, "\n")] = 0; // Enlève le retour à la ligne si présent

    // Envoie de la commande CONNECT <pseudo> au serveur
    char connect_cmd[BUFFER_SIZE];
    snprintf(connect_cmd, sizeof(connect_cmd), "CONNECT %s", pseudo);
    send(sock, connect_cmd, strlen(connect_cmd), 0);
    printf("Connect envoyé : %s\n", connect_cmd);
    printf("Envoi : %s\n", send_cmd);


    // Instructions à l'utilisateur
    printf("Vous êtes connecté. Tapez SEND <message> pour parler, JOIN <salon> pour changer de salon, ou DISCONNECT pour quitter.\n\n");

    fd_set readfds; // Ensemble de fichiers surveillés (clavier + socket)
    while (1) {
        FD_ZERO(&readfds);              // Réinitialise l'ensemble
        FD_SET(STDIN_FILENO, &readfds); // Ajoute le clavier (entrée standard)
        FD_SET(sock, &readfds);         // Ajoute le socket du serveur

        int max_fd = sock > STDIN_FILENO ? sock + 1 : STDIN_FILENO + 1;
        if (select(max_fd, &readfds, NULL, NULL, NULL) < 0) {
            perror("select");
            break;
        }

        // Si l'utilisateur a tapé quelque chose au clavier
        if (FD_ISSET(STDIN_FILENO, &readfds)) {
            memset(buffer, 0, BUFFER_SIZE);
            fgets(buffer, BUFFER_SIZE, stdin);
            buffer[strcspn(buffer, "\n")] = 0; // Enlève le \n
            // Si l'utilisateur veut quitter
            if (strncmp(buffer, "EXIT", 4) == 0 || strncmp(buffer, "DISCONNECT", 10) == 0) {
                send(sock, "DISCONNECT", 10, 0);
                break; // On quitte la boucle
            }
            // Si c'est une commande reconnue : JOIN ou SEND déjà formée
            else if (strncmp(buffer, "JOIN ", 5) == 0 || strncmp(buffer, "SEND ", 5) == 0) {
                send(sock, buffer, strlen(buffer), 0);
            }
            // Sinon, on formate automatiquement un message classique
            else {
                char send_cmd[BUFFER_SIZE];
                snprintf(send_cmd, sizeof(send_cmd), "SEND [%s] %.900s", pseudo, buffer); // On ajoute le pseudo dans le message
                send(sock, send_cmd, strlen(send_cmd), 0);
            }
        }

        // Si on reçoit un message du serveur
        if (FD_ISSET(sock, &readfds)) {
            memset(buffer, 0, BUFFER_SIZE);
            int valread = recv(sock, buffer, BUFFER_SIZE, 0);
            if (valread <= 0) {
                printf("Déconnecté du serveur.\n");
                break;
            }
            printf("%s", buffer); // Affiche le message reçu
        }
    }

    // Fermeture de la socket à la fin
    close(sock);
    return 0;
}