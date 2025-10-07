<?php
class GError {
    public const notFound = 404;
    public const forbidden = 403;
    public const badRequest = 400;
    public const unauthorized = 401;
    public const inclusive = "i";
    public const exclusive = "e";
    public const all_match = "a";
    public const strict_exclude = "s";

    private string $name;
    private int $errorType;
    private string $filterType;
    private string $origin;
    private bool $autoThrow;
    private array $residue;
    private array $samples;
    private string $errMessage;
    private array $errDetails;

    public function __construct(string $name, int $errorType, string $filterType, array $samples, ?string $origin = "origen desconocido", ?bool $autoThrow = false, ?string $errMessage = "Error encontrado") {
        $this->name = $name;
        $this->errorType = $errorType;
        $this->filterType = $filterType;
        $this->origin = $origin;
        $this->autoThrow = $autoThrow;
        $this->samples = $samples;
        $this->errMessage = $errMessage;
        $this->errDetails = [];
        $this->residue = [];
    }

    public function filter($input): mixed {
        switch ($this->filterType) {
            case self::inclusive:
                foreach ($this->samples as $key => $sample) {
                    if (is_callable($sample)) {
                        if ($sample($input)) {
                            return $input;
                        }
                    } else {
                        if ($input === $key || $input === $sample) {
                            return $input;
                        }
                    }
                }
                
                if ($this->autoThrow) {
                    $this->residue[] = $input;
                    $this->throw();
                } else {
                    $this->residue[] = $input;
                    return null;
                }
                
            case self::exclusive:
                foreach ($this->samples as $key => $sample) {
                    $shouldExclude = false;
                    
                    if (is_callable($sample)) {
                        if ($sample($input)) {
                            $shouldExclude = true;

                            if (!is_int($key)) {
                                $this->errDetails[] = $key;
                            }
                        }
                    } else {
                        if ($input === $key || $input === $sample) {
                            $shouldExclude = true;

                            if (!is_int($key)) {
                                $this->errDetails[] = $sample;
                            }
                        }
                    }
                    
                    if ($shouldExclude) {
                        if ($this->autoThrow) {
                            $this->residue[] = $input;
                            $this->throw();
                        } else {
                            $this->residue[] = $input;
                        }
                        return null;
                    }
                }
                
                return $input;
                
            case self::all_match:
                $shouldInclude = true;

                foreach ($this->samples as $key => $sample) {
                    if (is_callable($sample)) {
                        if (!$sample($input)) {
                            if (!is_int($key)) {
                                $this->errDetails[] = $key;
                            }
                            
                            $shouldInclude = false;
                        }
                    } else {
                        if ($input !== $key && $input !== $sample) {
                            if (!is_int($key) || is_string($sample)) {
                                $this->errDetails[] = $sample;
                            }

                            $shouldInclude = false;
                        }
                    }
                }

                if ($shouldInclude) {
                    return $input;
                }
                
                if ($this->autoThrow) {
                    $this->residue[] = $input;
                    $this->throw();
                } else {
                    $this->residue[] = $input;
                    return null;
                }

            case self::strict_exclude:
                $allMatchExclusion = true;

                foreach ($this->samples as $key => $sample) {
                    $currentExcludes = false;
                    
                    if (is_callable($sample)) {
                        if ($sample($input)) {
                            $currentExcludes = true;
                            if (!is_int($key)) {
                                $this->errDetails[] = $key;
                            }
                        }
                    } else {
                        if ($input === $key || $input === $sample) {
                            $currentExcludes = true;
                            if (!is_int($key)) {
                                $this->errDetails[] = $sample;
                            }
                        }
                    }
                    
                    if (!$currentExcludes) {
                        $allMatchExclusion = false;
                    }
                }

                if ($allMatchExclusion) {
                    if ($this->autoThrow) {
                        $this->residue[] = $input;
                        $this->throw();
                    } else {
                        $this->residue[] = $input;
                    }
                    return null;
                }
                
                return $input;
        }
        return null;
    }   

    public function throw(): void {
        if (!empty($this->residue)) {
            $this->residue = [];
            throw new Exception(json_encode([
                "status" => $this->errorType,
                "ErrMessage" => $this->errMessage,
                "errDetails" => $this->errDetails
            ]));
        }
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setErrorType(int $type): void {
        $this->errorType = $type;
    }

    public function setFilterType(string $type): void {
        if (!in_array($type, [self::inclusive, self::exclusive], true)) {
            throw new InvalidArgumentException("filterType debe ser 'i' o 'e'");
        }
        $this->filterType = $type;
    }

    public function setOrigin(string $origin): void {
        $this->origin = $origin;
    }

    public function setAutoThrow(bool $throw): void {
        $this->autoThrow = $throw;
    }

    public function setSamples(array $samples): void {
        $this->samples = $samples;
    }

    public function setErrMessage(string $message): void {
        $this->errMessage = $message;
    }

    // Getters
    public function getName(): string {
        return $this->name;
    }

    public function getErrorType(): int {
        return $this->errorType;
    }

    public function getFilterType(): string {
        return $this->filterType;
    }

    public function getOrigin(): string {
        return $this->origin;
    }

    public function getAutoThrow(): bool {
        return $this->autoThrow;
    }

    public function getSamples(): array {
        return $this->samples;
    }

    public function getErrMessage(): string {
        return $this->errMessage;
    }

    public function getErrDetails(): array {
        return $this->errDetails;
    }

    public function getResidue(): array {
        return $this->residue;
    }

}