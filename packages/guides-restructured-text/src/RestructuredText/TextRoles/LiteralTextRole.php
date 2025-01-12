<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\ParserContext;

class LiteralTextRole implements TextRole
{
    final public const NAME = 'literal';

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
        string $rawContent,
    ): GenericTextRoleInlineNode {
        return new GenericTextRoleInlineNode('literal', $rawContent);
    }
}
