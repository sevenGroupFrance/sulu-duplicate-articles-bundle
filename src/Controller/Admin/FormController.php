<?php

namespace SevenGroupFrance\SuluDuplicateArticlesBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FormController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    // Dupliquer le formulaire
    public function duplicateForm(Request $request)
    {
        $content = $request->getContent();
        $requestData = json_decode($content, true);
        $originalFormId = $requestData['uid'];

        // Connexion à notre base de données.
        $connection = $this->entityManager->getConnection();

        // Dupliquer la ligne du formulaire.
        $query = "INSERT INTO fo_forms (defaultLocale) 
            SELECT defaultLocale FROM fo_forms 
            WHERE id = :originalFormId";
        $params = ['originalFormId' => $originalFormId];
        $connection->executeQuery($query, $params);

        // Obtenir l'ID du nouveau formulaire inséré.
        $newFormId = $connection->lastInsertId();

        // Dupliquer les champs du formulaire.
        $query = "INSERT INTO fo_form_fields (keyName, orderNumber, type, width, required, defaultLocale, idForms) 
            SELECT keyName, orderNumber, type, width, required, defaultLocale, :newFormId 
            FROM fo_form_fields 
            WHERE idForms = :originalFormId";
        $params = ['newFormId' => $newFormId, 'originalFormId' => $originalFormId];
        $connection->executeQuery($query, $params);

        // Obtenir l'utilisateur actuel.
        $user = $this->security->getUser();
        $currentUserId = $user->getId();

        // Obtenir la date et l'heure actuelles.
        $currentDateTime = new DateTime();

        // Dupliquer les traductions du formulaire avec des titres, dates et informations utilisateur modifiés.
        $query = "INSERT INTO fo_form_translations 
          (title, subject, fromEmail, fromName, toEmail, toName, mailText, submitLabel, successText, 
          sendAttachments, deactivateAttachmentSave, deactivateNotifyMails, deactivateCustomerMails, 
          replyTo, Locale, created, changed, idForms, idUsersCreator, idUsersChanger)
          SELECT CONCAT('copie (', title, ')'), subject, fromEmail, fromName, toEmail, toName, mailText, 
                 submitLabel, successText, sendAttachments, deactivateAttachmentSave, deactivateNotifyMails, 
                 deactivateCustomerMails, replyTo, Locale, :currentDateTime, :currentDateTime, 
                 :newFormId, :currentUserId, :currentUserId 
          FROM fo_form_translations 
          WHERE idForms = :originalFormId";
        $params = [
            'newFormId' => $newFormId,
            'originalFormId' => $originalFormId,
            'currentDateTime' => $currentDateTime->format('Y-m-d H:i:s'),
            'currentUserId' => $currentUserId,
        ];
        $connection->executeQuery($query, $params);

        return new JsonResponse(['message' => 'Formulaire dupliqué avec succès'], 200);
    }
}
