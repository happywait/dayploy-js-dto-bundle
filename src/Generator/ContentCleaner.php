<?php

namespace Dayploy\JsDtoBundle\Generator;

class ContentCleaner
{
    public function removeTrailingSpacesAndTab(
        string $content,
    ): string {
        $pattern = '/[ ]*\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
    }

    public function removeDoubleEndLine(
        string $content,
    ): string {
        $pattern = '/\n\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
    }

    public function removeLeadingNewLines(
        string $content,
    ): string {
        // Utiliser une expression régulière qui capture les nouvelles lignes (\n ou \r) en début de texte
        $pattern = '/^\s*\n+/';
        // Remplacer les lignes vides en début de texte par une chaîne vide
        $cleanedContent = preg_replace($pattern, '', $content);

        return $cleanedContent;
    }
}
