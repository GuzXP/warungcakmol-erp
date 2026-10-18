<?php
$hashes = [
    'admin' => '$2a$12$Al3LGNkZf6OL1iLXFx33bue8DnU4WFwIyIYz7zl42P3fVCscAJN9a',
    'adi' => '$2y$10$USOySsyVI901K2h5JEHK0exqZzYjxG0or4b29jFfjh9MUk28R84iS',
    'essy123' => '$2y$10$L.uD9nqc/p7.gPttHTlDYedEfTRu3SNFRZ3oF8ucImJ6s2OpSPfta',
    'foo' => '$2y$10$MFktFLe0BfywsteXSbFNlOIT2RQT4JwJ9o9czGKQ9bDtZMOxNAa6q',
    'ade123' => '$2y$10$Ls8Brj2eiQKedaZyNLfwLONSH/IEAqTimSZ0SQCd/0t8vNhS0rO2K',
    'kitchen' => '$2y$10$/1rC6MEpdufG3GJKADGrD.AGyXmAmmVQ0c7RErciWrpjF9PELauUm',
];

$guesses = ['admin', 'admin123', 'password', '123456', 'adi', 'adi123', 'essy', 'essy123', 'foo', 'kitchen', 'ade', 'ade123', 'owner'];

foreach ($hashes as $name => $hash) {
    foreach ($guesses as $guess) {
        if (password_verify($guess, $hash)) {
            echo "$name matches $guess\n";
        }
    }
}
