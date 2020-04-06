<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class DocumentVoter extends Voter
{
    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $document;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Document;
    }

    protected function voteOnAttribute($attribute, $document, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->document = $document;

        if (!$this->currentUser) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView();
                break;
            case 'EDIT':
                return $this->canEdit();
                break;
            case 'DELETE':
                return $this->canDelete();
                break;
        }

        return false;
    }

    protected function canView()
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()) {
            return true;
        }

        $user = $this->document->getCreatedBy();
        foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
            foreach ($user->getServiceUser() as $serviceUser) {
                if ($serviceCurrentUser->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->isAdminUser())) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->isAdminUser())) {
            return true;
        }

        return false;
    }

    // If current user is administrator
    protected function isAdminUser()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
                foreach ($this->document->getCreatedBy()->getServiceUser() as $serviceUser) {
                    if ($serviceUser->getService()->getId() == $serviceCurrentUser->getService()->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
