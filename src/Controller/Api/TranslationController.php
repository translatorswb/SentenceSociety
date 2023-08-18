<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Service\ITimeService;
use App\Service\TranslationService;
use App\Service\TranslationSessionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationController extends AbstractController
{
    /**
     * @var TranslationService
     */
    private $translationService;

    /** @var TranslationSessionService */
    private $translationSessionService;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ITimeService
     */
    private $timeService;

    public function __construct(TranslationService $translationService, TranslationSessionService $translationSessionService, UserRepository $userRepository ,ITimeService $timeService)
    {
        $this->translationService = $translationService;
        $this->translationSessionService = $translationSessionService;
        $this->userRepository = $userRepository;
        $this->timeService = $timeService;
    }


    public function nextSourceAssignment(LoggerInterface $logger)
    {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $source = $this->translationService->getNextSource($translationSession);

        if ($source) {
            return $this->json(['data' => [
                'id' => $source->getId(),
                'text' => $source->getText(),
            ]]);
        } else {
            return $this->json(['errors' => [[
                'title' => 'no more translation sources available'
            ]]], Response::HTTP_NOT_FOUND);
        }

    }

    public function skipAndNextSourceAssignment(LoggerInterface $logger, $sourceId)
    {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $source = $this->translationService->skipAndNextSource($translationSession, $sourceId);
        if ($source) {
            return $this->json(['data' => [
                'id' => $source->getId(),
                'text' => $source->getText(),
            ]]);
        } else {
            return $this->json(['errors' => [[
                'title' => 'no more translation sources available'
            ]]], Response::HTTP_NOT_FOUND);
        }
    }

    public function nextTargetAssignment()
    {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $target = $this->translationService->getNextTarget($translationSession);
        // or use serializer?
        if ($target !== null) {
            return $this->json(['data' => [
                'id' => $target->getId(),
                'text' => $target->getText(),
                'source' => $target->getSource()->getText(),
            ]]);
        } else {
            return $this->json(['errors' => [[
                'title' => 'no more translation targets available'
            ]]], Response::HTTP_NOT_FOUND);
        }


    }

    public function skipAndNewTargetAssignment($targetId)
    {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $target = $this->translationService->skipAndNewValidateAssignment($translationSession, $targetId);

        if ($target !== null) {
            return $this->json(['data' => [
                'id' => $target->getId(),
                'text' => $target->getText(),
                'source' => $target->getSource()->getText(),
            ]]);
        } else {
            return $this->json(['errors' => [[
                'title' => 'no more translation sources available'
            ]]], Response::HTTP_NOT_FOUND);
        }
    }


    public function flagSource($sourceId) {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $this->translationService->flagSource($translationSession, $sourceId);
        return $this->json('thanks!');
    }

    public function addTarget(Request $request, $sourceId) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $text = $parsedContent->text;
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $this->translationService->addTranslationForSource($translationSession, $sourceId, $text);

        $userId = $translationSession->getUserId();
        if ($userId) {
            $user = $this->userRepository->find($userId);
            $this->translationSessionService->addRoundBonusToUser($user, $this->timeService->currentDateTime());
        }

        return $this->json('thanks!');
    }

    public function rateTarget(Request $request, $targetId) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $rating = $parsedContent->rating;
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $this->translationService->validateTarget($translationSession, $targetId, $rating);
        return $this->json(true);
    }

    public function flagTarget($targetId) {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $this->translationService->flagTarget($translationSession, $targetId);
        $this->translationSessionService->saveSession();
        return $this->skipAndNewTargetAssignment($targetId);
    }
}