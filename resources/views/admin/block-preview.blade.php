<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        @viteReactRefresh
        @vite(['resources/js/block-preview.tsx'])
    </head>
    <body>
        <div id="preview-root"></div>
        <script>
            window.__PREVIEW_BLOCK__ = @json($block);
        </script>
    </body>
</html>
