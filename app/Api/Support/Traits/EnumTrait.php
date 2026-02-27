<?php

declare(strict_types=1);

namespace App\Api\Support\Traits;

use ValueError;

/**
 * Trait utilitário para enums backed (string|int), fornecendo
 * métodos auxiliares de consulta, comparação e listagem.
 */
trait EnumTrait
{
    /**
     * Retorna os valores de todos os cases.
     *
     * @return list<string|int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna os nomes de todos os cases.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Retorna um mapa associativo [nome => valor].
     *
     * @return array<string, string|int>
     */
    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    /**
     * Retorna o valor de um case pelo seu nome.
     */
    public static function valueOf(string $name): string|int
    {
        return self::caseOf($name)->value;
    }

    /**
     * Retorna a instância do case pelo seu nome.
     */
    public static function caseOf(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new ValueError(sprintf('"%s" is not a valid name for enum "%s"', $name, self::class));
    }

    /**
     * Verifica se o valor do enum é igual ao informado (string, int ou instância).
     */
    public function equals(string|int|self $value): bool
    {
        if ($value instanceof self) {
            return $this === $value;
        }

        return $this->value === $value;
    }

    /**
     * Verifica se o enum está contido no array informado.
     *
     * @param list<self> $cases
     */
    public function has(array $cases): bool
    {
        return in_array($this, $cases, true);
    }

    /**
     * Retorna a quantidade total de cases.
     */
    public static function count(): int
    {
        return count(self::cases());
    }

    /**
     * Executa um método em todos os cases e retorna os resultados.
     *
     * @return list<mixed>
     */
    public static function all(string $method): array
    {
        return array_map(fn (self $case) => $case->$method(), self::cases());
    }
}
