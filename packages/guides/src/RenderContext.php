<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides;

use League\Flysystem\FilesystemInterface;
use League\Uri\Uri;
use League\Uri\UriInfo;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\DocumentNode;
use function dirname;
use function ltrim;
use function trim;

class RenderContext
{
    private UrlGeneratorInterface $urlGenerator;

    private string $currentFileName;

    private FilesystemInterface $origin;

    private Metas $metas;

    private string $destinationPath;

    private string $outputFormat;
    private DocumentNode $document;
    private FilesystemInterface $destination;

    private function __construct(
        string $outputFolder,
        string $currentFileName,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        Metas $metas,
        UrlGeneratorInterface $urlGenerator,
        string $outputFormat
    ) {
        $this->currentFileName = $currentFileName;
        $this->destinationPath = trim($outputFolder, '/');
        $this->origin = $origin;
        $this->urlGenerator = $urlGenerator;
        $this->metas = $metas;
        $this->outputFormat = $outputFormat;
        $this->destination = $destination;
    }

    public static function forDocument(
        DocumentNode $documentNode,
        FilesystemInterface $origin,
        FilesystemInterface $destination,
        string $destinationPath,
        Metas $metas,
        UrlGeneratorInterface $urlGenerator,
        string $ouputFormat
    ): self {
        $self = new self(
            $destinationPath,
            $documentNode->getFilePath(),
            $origin,
            $destination,
            $metas,
            $urlGenerator,
            $ouputFormat
        );

        $self->document = $documentNode;

        return $self;
    }

    /**
     * @template TType as mixed
     * @param TType|null $default
     *
     * @return ($default is null ? mixed|null: TType|string|Node)
     */
    public function getVariable(string $variable, $default = null)
    {
        return $this->document->getVariable($variable, $default);
    }

    public function getLink(string $name, bool $relative = true): string
    {
        $link = $this->document->getLink($name);

        if ($link !== null) {
            if ($relative) {
                return $this->urlGenerator->relativeUrl($link);
            }

            return $link;
        }

        return '';
    }

    public function canonicalUrl(string $url): ?string
    {
        return $this->urlGenerator->canonicalUrl($this->getDirName(), $url);
    }

    public function relativeDocUrl(string $filename, ?string $anchor = null): string
    {
        if (UriInfo::isAbsolutePath(Uri::createFromString($filename))) {
            return $this->destinationPath . $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor);
        }

        $baseUrl = ltrim($this->urlGenerator->absoluteUrl($this->destinationPath, $this->getDirName()), '/');

        if ($this->metas->findDocument($filename) !== null) {
            return $this->destinationPath . '/'
                . $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor);
        }

        return $this->urlGenerator->canonicalUrl(
            $baseUrl,
            $this->urlGenerator->createFileUrl($filename, $this->outputFormat, $anchor)
        );
    }

    private function getDirName(): string
    {
        $dirname = dirname($this->currentFileName);

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    public function getCurrentFileName(): string
    {
        return $this->currentFileName;
    }

    public function getOrigin(): FilesystemInterface
    {
        return $this->origin;
    }

    public function getMetas(): Metas
    {
        return $this->metas;
    }

    public function getMetaEntry(): ?DocumentEntry
    {
        return $this->metas->findDocument($this->currentFileName);
    }

    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    public function setDestinationPath(string $path): void
    {
        $this->destinationPath = $path;
    }

    public function getDestination(): FilesystemInterface
    {
        return $this->destination;
    }

    public function getCurrentFileDestination(): string
    {
        return $this->destinationPath . '/' . $this->currentFileName . '.' . $this->outputFormat;
    }
}
