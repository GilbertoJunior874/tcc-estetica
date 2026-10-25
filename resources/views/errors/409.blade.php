<x-error-page code="409" title="Ação não permitida" :message="$exception->getMessage() ?: 'A situação do registro mudou e esta ação não pode mais ser realizada. Volte e atualize a página.'" />
