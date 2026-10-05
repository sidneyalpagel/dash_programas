{{-- Estilos inline: o CSS pré-compilado do Filament não inclui utilitários arbitrários. --}}
@if (empty($linhas))
    <p style="font-size: .875rem; opacity: .7">Não há diferenças em relação à versão publicada.</p>
@else
    <div style="overflow-x: auto">
        <table style="width: 100%; font-size: .875rem; border-collapse: collapse">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid rgba(127,127,127,.3)">
                    <th style="padding: .5rem 1rem .5rem 0; font-weight: 600">Campo</th>
                    <th style="padding: .5rem 1rem .5rem 0; font-weight: 600">{{ $rotuloDe ?? 'Antes' }}</th>
                    <th style="padding: .5rem 0; font-weight: 600">{{ $rotuloPara ?? 'Depois' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr style="vertical-align: top; border-bottom: 1px solid rgba(127,127,127,.15)">
                        <td style="padding: .5rem 1rem .5rem 0; font-weight: 500; white-space: nowrap">{{ $linha['campo'] }}</td>
                        <td style="padding: .5rem 1rem .5rem 0; opacity: .6; text-decoration: line-through">{{ $linha['de'] }}</td>
                        <td style="padding: .5rem 0">{{ $linha['para'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
