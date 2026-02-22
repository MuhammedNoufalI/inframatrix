<div class="flex flex-col gap-y-1">
    @php
        $activeServerIds = $this->getTableFilterState('server')['values'] ?? [];
        $activeGitProviderIds = $this->getTableFilterState('git_provider')['values'] ?? [];
        
        $activeIntegrationTypeIds = $this->getTableFilterState('integration_type')['values'] ?? [];
        $activeAccountIds = $this->getTableFilterState('account')['values'] ?? [];
        $integrationValueFilter = $this->getTableFilterState('integration_value')['value'] ?? null;
    @endphp

    <ul class="list-disc ms-4 text-sm">
        @foreach ($getRecord()->environments as $environment)
            @php
                $badges = [];
                
                // Server Filter Match
                if (!empty($activeServerIds) && in_array($environment->server_id, $activeServerIds)) {
                    $badges[] = 'Server: ' . ($environment->server->server_name ?? $environment->server_id);
                }

                // Git Provider Filter Match
                if (!empty($activeGitProviderIds) && in_array($environment->git_provider_id, $activeGitProviderIds)) {
                    $badges[] = 'Git Provider: ' . ($environment->gitProvider->name ?? $environment->git_provider_id);
                }

                // Integration Logic Match
                if (!empty($activeIntegrationTypeIds) || !empty($activeAccountIds) || !empty($integrationValueFilter)) {
                    $matchedIntegrations = [];

                    foreach ($environment->integrations as $integration) {
                        $match = true;
                        
                        if (!empty($activeIntegrationTypeIds) && !in_array($integration->integration_type_id, $activeIntegrationTypeIds)) {
                            $match = false;
                        }
                        
                        if (!empty($activeAccountIds) && !in_array($integration->account_id, $activeAccountIds)) {
                            $match = false;
                        }
                        
                        if (!empty($integrationValueFilter) && stripos($integration->value, $integrationValueFilter) === false) {
                            $match = false;
                        }
                        
                        if ($match) {
                            $labelParts = [];
                            if ($integration->integrationType) {
                                $labelParts[] = $integration->integrationType->name;
                            }
                            if ($integration->account) {
                                $labelParts[] = $integration->account->account_name;
                            } elseif ($integration->value) {
                                $labelParts[] = $integration->value;
                            }
                            
                            $matchedIntegrations[] = implode(' - ', $labelParts);
                        }
                    }

                    if (count($matchedIntegrations) > 0) {
                        foreach (array_unique($matchedIntegrations) as $integrationLabel) {
                            $badges[] = $integrationLabel;
                        }
                    }
                }
            @endphp

            <li class="py-1">
                <span class="font-medium capitalize text-gray-500">{{ $environment->type }}:</span> 
                <a href="{{ $environment->url }}" target="_blank" class="text-primary-600 hover:underline">{{ $environment->url }}</a>
                
                @if (count($badges) > 0)
                    <div class="inline-flex flex-wrap gap-1 mt-1 lg:ml-2">
                        @foreach($badges as $badge)
                            <span class="inline-flex items-center gap-1 min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-xl bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 ring-1 ring-inset ring-primary-600/20 dark:ring-primary-400/30">
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $badge }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
