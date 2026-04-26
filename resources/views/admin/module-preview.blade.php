<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        @viteReactRefresh
        @vite(['resources/js/module-preview.tsx'])
    </head>
    <body>
        <div id="module-preview-root"></div>
        <script>
            window.__PREVIEW_BLOCKS__ = @json($blocks);
        </script>
    </body>
</html>
