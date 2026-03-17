
#include <stdio.h>              
#include <stdlib.h>             
#include <string.h>             
#include <unistd.h>             
#include <arpa/inet.h>          
#include <pthread.h>           

#define PORT 8080               // Port du serveur
#define MAX_CLIENTS 100         // Nombre maximal de clients connectés en même temps
#define BUFFER_SIZE 1024        // Taille du buffer utilisé pour les messages

// Structure pour stocker les informations d'un client connecté
struct client_t {
    int socket;                 // Socket du client
    char pseudo[32];           // Pseudo choisi par le client
    char salon[32];            // Nom du salon dans lequel il se trouve
};

struct client_t clients[MAX_CLIENTS]; // Tableau de tous les clients connectés
int nb_clients = 0;                   // Compteur de clients actifs
pthread_mutex_t mutex = PTHREAD_MUTEX_INITIALIZER; // Mutex pour sécuriser l'accès concurrent

// Fonction exécutée par un thread pour chaque client connecté
void *gestion_client(void *arg) {
    int sock = *(int *)arg;
    free(arg);
    char buffer[BUFFER_SIZE];     // Buffer pour stocker les messages reçus
    char pseudo[32] = "";         // Pseudo du client courant
    char salon[32] = "general";   // Salon par défaut

    while (1) {
        memset(buffer, 0, BUFFER_SIZE);
        int lu = recv(sock, buffer, BUFFER_SIZE, 0); // Lecture du message depuis le socket
        if (lu <= 0) {
            // Si le client se déconnecte
            pthread_mutex_lock(&mutex);
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket == sock) {
                    // Informer les autres clients de la déconnexion
                    char msg[BUFFER_SIZE];
                    snprintf(msg, sizeof(msg), "[SERVEUR] %s a quitté le salon #%s\n", clients[i].pseudo, clients[i].salon);
                    for (int j = 0; j < nb_clients; j++) {
                        if (clients[j].socket != sock && strcmp(clients[j].salon, clients[i].salon) == 0) {
                            send(clients[j].socket, msg, strlen(msg), 0);
                        }
                    }
                    // Retirer le client du tableau
                    clients[i] = clients[nb_clients - 1];
                    nb_clients--;
                    break;
                }
            }
            pthread_mutex_unlock(&mutex);
            close(sock);
            pthread_exit(NULL);
        }
        printf("[DEBUG] Reçu : %s\n", buffer);

        buffer[strcspn(buffer, "\n")] = 0; // Supprimer le retour à la ligne

        // Si le client s'identifie
        if (strncmp(buffer, "CONNECT ", 8) == 0) {
            strncpy(pseudo, buffer + 8, sizeof(pseudo));
            pthread_mutex_lock(&mutex);
            strcpy(clients[nb_clients].pseudo, pseudo);
            strcpy(clients[nb_clients].salon, salon);
            clients[nb_clients].socket = sock;
            nb_clients++;
            pthread_mutex_unlock(&mutex);
            // Message de bienvenue aux autres
            char msg[BUFFER_SIZE];
            snprintf(msg, sizeof(msg), "[SERVEUR] %s a rejoint le salon #%s\n", pseudo, salon);
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket != sock && strcmp(clients[i].salon, salon) == 0) {
                    send(clients[i].socket, msg, strlen(msg), 0);
                }
            }
            printf("[DEBUG] Nouveau client connecté : %s\n", pseudo);
        }
        // Si le client envoie un message
        else if (strncmp(buffer, "SEND ", 5) == 0) {
            char msg[BUFFER_SIZE];
            snprintf(msg, sizeof(msg), "%s\n", buffer + 5);
            pthread_mutex_lock(&mutex);
            char salon_courant[32] = "general";
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket == sock) {
                    strncpy(salon_courant, clients[i].salon, sizeof(salon_courant));
                    break;
                }
            }
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket != sock && strcmp(clients[i].salon, salon_courant) == 0) {
                    send(clients[i].socket, msg, strlen(msg), 0);
                }
            }
            pthread_mutex_unlock(&mutex);
        }
        // Si le client change de salon
        else if (strncmp(buffer, "JOIN ", 5) == 0) {
            char nouveau_salon[32];
            strncpy(nouveau_salon, buffer + 5, sizeof(nouveau_salon));
            pthread_mutex_lock(&mutex);
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket == sock) {
                    strcpy(clients[i].salon, nouveau_salon);
                    break;
                }
            }
            pthread_mutex_unlock(&mutex);
            // Message aux autres du nouveau salon
            char msg[BUFFER_SIZE];
            snprintf(msg, sizeof(msg), "[SERVEUR] %s a rejoint le salon #%s\n", pseudo, nouveau_salon);
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket != sock && strcmp(clients[i].salon, nouveau_salon) == 0) {
                    send(clients[i].socket, msg, strlen(msg), 0);
                }
            }
        }
        // Si le client demande à se déconnecter
        else if (strncmp(buffer, "DISCONNECT", 10) == 0) {
            pthread_mutex_lock(&mutex);
            for (int i = 0; i < nb_clients; i++) {
                if (clients[i].socket == sock) {
                    char msg[BUFFER_SIZE];
                    snprintf(msg, sizeof(msg), "[SERVEUR] %s s'est déconnecté.\n", clients[i].pseudo);
                    for (int j = 0; j < nb_clients; j++) {
                        if (clients[j].socket != sock && strcmp(clients[j].salon, clients[i].salon) == 0) {
                            send(clients[j].socket, msg, strlen(msg), 0);
                        }
                    }
                    clients[i] = clients[nb_clients - 1];
                    nb_clients--;
                    break;
                }
            }
            pthread_mutex_unlock(&mutex);
            close(sock);
            pthread_exit(NULL);
        }
    }
    return NULL;
}

int main() {
    int serveur, *nouv_sock;
    struct sockaddr_in adresse;
    socklen_t taille = sizeof(adresse);

    // Création du socket serveur
    serveur = socket(AF_INET, SOCK_STREAM, 0);
    if (serveur == -1) {
        perror("socket");
        exit(1);
    }

    // Configuration des infos du serveur
    adresse.sin_family = AF_INET;
    adresse.sin_addr.s_addr = INADDR_ANY;
    adresse.sin_port = htons(PORT);

    // Liaison du socket à l'adresse et au port
    if (bind(serveur, (struct sockaddr *)&adresse, sizeof(adresse)) < 0) {
        perror("bind");
        exit(1);
    }

    // Mise en écoute des connexions entrantes
    if (listen(serveur, 10) < 0) {
        perror("listen");
        exit(1);
    }

    printf("[SERVEUR] En écoute sur le port %d\n", PORT);

    // Boucle principale d'acceptation de nouveaux clients
    while (1) {
        nouv_sock = malloc(sizeof(int));
        *nouv_sock = accept(serveur, (struct sockaddr *)&adresse, &taille);
        if (*nouv_sock < 0) {
            perror("accept");
            continue;
        }

        // Création d'un thread pour gérer ce nouveau client
        pthread_t th;
        pthread_create(&th, NULL, gestion_client, nouv_sock);
        pthread_detach(th); // Pas besoin de join, thread détaché
    }

    // Fermeture du serveur (non atteignable ici)
    close(serveur);
    return 0;
}
