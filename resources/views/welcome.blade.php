@extends('layouts.app')
@section('title', 'मेयाङ्बो वंशावली — सरल तर विस्तृत')

@section('content')


                <!-- At a glance -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
                    <h2 class="text-lg font-semibold">एक नजरमा</h2>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">मूल पुर्खा</div>
                            <div>थिन्दोलुङ खोयाहाङ</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">केन्द्र</div>
                            <div>हस्तपुर यक (याङरुप), थेचम्बु (ताप्लेजुङ), फावाखोला/साङ्बाङ्गु</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">कालखण्ड</div>
                            <div>ई.पू. १०० – ई. ३००; हालसम्म ~३१ पुस्ता</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">थर रूपान्तरण</div>
                            <div>खोयाहाङ → “मेयाङ्बो” (मेयाङ्खुन = नभेटियो)</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">सम्बद्ध शाखा</div>
                            <div>आङ्बोहाङ, उसुक, खिम्दिङ आदि</div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <div class="text-xs text-slate-500">स्रोत/उल्लेख</div>
                            <div>स्व. इमानसिंह चेम्जोङका कृतिहरू, लिम्बुवान अध्ययन</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TIMELINE -->
            <section class="mt-8">
                <h2 class="text-lg font-semibold">मुख्य घटनाक्रम (Timeline)</h2>

                <ol class="relative mt-4 border-s-2 border-slate-200 ps-5 space-y-4">
                    <!-- Item -->
                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">
                                सिताङ्गे उपत्यकाबाट आगमन
                                <span
                                    class="ms-2 inline-block align-middle text-[11px] font-medium rounded-full border px-2 py-0.5 text-slate-700">ई.पू.
                                    ६औँ शताब्दी (परम्परा)</span>
                            </h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>सानमकवान वंशी १० सरदार र ३ पुरोहित: सिताङ्गे → आसाम → उत्तर बंगाल हुँदै लिम्बुवान।</li>
                                <li>त्यसबेला ८ अपुङ्गी राजाबाट अनुमति लिएर बसोबास।</li>
                            </ul>
                        </div>
                    </li>

                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">
                                याङरुप (हस्तपुर यक)मा राज्य
                                <span
                                    class="ms-2 inline-block text-[11px] font-medium rounded-full border px-2 py-0.5 text-slate-700">ई.पू.
                                    १०० – ई. ३०० (अनुमान)</span>
                            </h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>थिन्दोलुङ खोयाहाङको शासन; खोयाङ्देन/थेचम्बु क्षेत्र पुर्खौली केन्द्र।</li>
                            </ul>
                        </div>
                    </li>

                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">योङ्हाङ आक्रमण र थेचम्बु</h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>हस्तपुर गढीमाथि चढाइपछि हाङ्सामबुन खोयाहाङ थेचम्बुमा पहिलो राजा।</li>
                                <li>“मेयाङ्खुन” (नभेटियो) बाट “मेयाङ्बो” नाम प्रसारित भएको परम्परा।</li>
                            </ul>
                        </div>
                    </li>

                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">
                                राजकुमारी थाङ्सामा र विजयपुर
                                <span
                                    class="ms-2 inline-block text-[11px] font-medium rounded-full border px-2 py-0.5 text-slate-700">वि.सं.
                                    १८१८–१८२६ (परम्परा)</span>
                            </h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>युहाङ्केपकी बहिनी थाङ्सामा र विजयपुरका राजा कामदत्त सेनबीच कुटुम्ब सम्बन्ध।</li>
                                <li>तावालुङ (तामाको मूर्ति) किवदन्ती; बराहक्षेत्र मन्दिरमा प्रतिष्ठा।</li>
                            </ul>
                        </div>
                    </li>

                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">सीमा सन्धि</h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>तमोर/ताम्बर–थेचम्बुबीच काभ्रे खोलामा पैतालाको छाप राखी सिमाङ्कन (मौखिक परम्परा)।</li>
                            </ul>
                        </div>
                    </li>

                    <li class="relative">
                        <span class="absolute -start-2.5 top-2 h-3 w-3 rounded-full bg-slate-900"></span>
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                            <h3 class="font-semibold">
                                गोरखा–लिम्बुवान सम्झौता
                                <span
                                    class="ms-2 inline-block text-[11px] font-medium rounded-full border px-2 py-0.5 text-slate-700">वि.सं.
                                    १८३१/०४/२२</span>
                            </h3>
                            <ul class="list-disc ms-5 mt-2 text-slate-700">
                                <li>स्थानीय अधिकार/राजसत्ताको निर्णायक मोड।</li>
                            </ul>
                        </div>
                    </li>
                </ol>
            </section>

            <!-- KEY FIGURES -->
           
<section class="mt-10">
  <div class="flex items-end justify-between gap-2">
    <h2 class="text-lg font-semibold">प्रमुख व्यक्तित्व</h2>
    <p class="text-xs text-slate-600">ऐतिहासिक व्यक्तित्वहरू — फोटो बिना पनि सुन्दर कार्डहरू</p>
  </div>

  <div
    x-data
    x-init="$el.querySelectorAll('[data-reveal]').forEach((el,i)=>{setTimeout(()=>{el.classList.remove('opacity-0','translate-y-4')}, i*70)})"
    class="mt-4 grid gap-4 sm:grid-cols-2 md:grid-cols-3"
  >
    <!-- थिन्दोलुङ खोयाहाङ -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-emerald-500 via-blue-500 to-fuchsia-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-100">
              <!-- crown -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M5 16h14l-1 4H6l-1-4zm15-9-4 3-4-6-4 6-4-3 2 9h12l2-9z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">थिन्दोलुङ खोयाहाङ</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-emerald-200 text-emerald-700 bg-emerald-50">मूल पुर्खा</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">खोयाङ्देन–थेचम्बु/याङरुप केन्द्रका शासक; चेम्जोङले उल्लेख।</p>
      </div>
    </article>

    <!-- हाङ्सामबुन -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-sky-500 via-indigo-500 to-cyan-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-sky-50 ring-1 ring-sky-100">
              <!-- shield -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2 4 6v6c0 5 3.8 9.3 8 10 4.2-.7 8-5 8-10V6l-8-4z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">हाङ्सामबुन खोयाहाङ</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-sky-200 text-sky-700 bg-sky-50">थेचम्बुको पहिलो राजा</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">हाङ्साम पोखरी स्थापना; नामाकरणसँग जोडिएको परम्परा।</p>
      </div>
    </article>

    <!-- लाहाङ मेयाङ्बो -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-rose-500 via-orange-500 to-amber-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-rose-50 ring-1 ring-rose-100">
              <!-- edit/rename -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">लाहाङ मेयाङ्बो</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-rose-200 text-rose-700 bg-rose-50">थर रूपान्तरण</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">युहाङ्केप पछिको पुस्तामा थर संस्थापन; आङ्बोहाङ शाखा।</p>
      </div>
    </article>

    <!-- राजकुमारी थाङ्सामा -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-fuchsia-500 via-purple-500 to-blue-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-fuchsia-50 ring-1 ring-fuchsia-100">
              <!-- link/relationship -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-fuchsia-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3.9 12a5 5 0 0 1 5-5h3v2h-3a3 3 0 1 0 0 6h3v2h-3a5 5 0 0 1-5-5zm7.1 1h2v-2h-2v2zm4-6a5 5 0 0 1 0 10h-3v-2h3a3 3 0 1 0 0-6h-3V7h3z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">राजकुमारी थाङ्सामा</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-fuchsia-200 text-fuchsia-700 bg-fuchsia-50">कुटुम्ब सम्बन्ध</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">विजयपुरसँग वैवाहिक सम्बन्ध; रानी/हाङ्साम पोखरी किवदन्ती।</p>
      </div>
    </article>

    <!-- शिदीहाङ -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-amber-500 via-lime-500 to-emerald-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-amber-50 ring-1 ring-amber-100">
              <!-- bow/defense (castle-ish) -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4 4h4v3H6v2h4V4h4v3h-2v2h4V4h4v8h-2v8H6v-8H4V4z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">शिदीहाङ (“नलेहाङ”)</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-amber-200 text-amber-700 bg-amber-50">फावाखोला शासक</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">चोक्सि डाँडा यक, धनु–विष प्रणाली र सीमासन्धि किवदन्ती।</p>
      </div>
    </article>

    <!-- केहरसिङ -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-teal-500 via-cyan-500 to-indigo-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="relative grid place-items-center w-10 h-10 rounded-xl bg-teal-50 ring-1 ring-teal-100">
              <!-- spark / agility -->
              <span class="absolute inline-flex h-8 w-8 rounded-full bg-teal-400/30 animate-ping"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="relative w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11 3 6 14h4l-1 7 6-10h-4l4-8z"/>
              </svg>
            </span>
            <div>
              <h3 class="font-semibold">केहरसिङ (“पखेटे”)</h3>
              <span class="inline-block text-[11px] mt-0.5 px-2 py-0.5 rounded-full ring-1 ring-teal-200 text-teal-700 bg-teal-50">लोककथा</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">अद्भुत शारीरिक कौशल; ‘पखेटे’ उपनाम र घटना।</p>
      </div>
    </article>

    <!-- Special: PDF link (no image) -->
    <article data-reveal
      class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 md:col-span-3">
      <div class="relative">
        <div class="h-2 bg-gradient-to-r from-cyan-500 via-teal-500 to-emerald-500"></div>
        <div class="px-4 pt-4">
          <div class="flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-cyan-50 ring-1 ring-cyan-100">
              <!-- document -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-cyan-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM8 12h8v2H8v-2zm0 4h8v2H8v-2zm6-9 5 5h-5V7z"/>
              </svg>
            </span>
            <div class="min-w-0">
              <h3 class="font-semibold truncate">मुन्‍धुम अनुसार सृष्टिको पहिलो मानव</h3>
              <p class="text-sm text-slate-600 mt-0.5">पूरा कागजात हेर्नुहोस् — शिक्षण/सन्दर्भका लागि उपयोगी।</p>
            </div>
            <a href="{{ asset('मुन्धुम अनुसार सृष्टिको पहिलो मानव.pdf') }}"
               class="ms-auto inline-flex items-center gap-2 rounded-lg bg-emerald-600 text-white px-3 py-1.5 text-sm font-semibold hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
              See More
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 3l7 7-7 7v-4H3v-6h11V3z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
      <div class="p-4">
        <p class="text-sm text-slate-700">
          संक्षेप सार: मुन्धुमको सृष्टि–वर्णनबारे जानकारी। थप पढ्न माथिको बटन प्रयोग गर्नुहोस्।
        </p>
      </div>
    </article>
  </div>
</section>








            <!-- PLACES & ETYMOLOGY -->
            <section class="mt-10">
                <h2 class="text-lg font-semibold">स्थान र नाम–व्युत्पत्ति</h2>

                <!-- grid -->
                <div x-data x-init="$el.querySelectorAll('[data-reveal]').forEach((el, i) => { setTimeout(() => { el.classList.remove('opacity-0', 'translate-y-4') }, i * 60) })" class="mt-4 grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Card -->
                    <article data-reveal
                        class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
                        <!-- header bar -->
                        <div class="relative">
                            <div class="h-2 bg-gradient-to-r from-emerald-500 via-blue-500 to-fuchsia-500"></div>
                            <div class="px-4 pt-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid place-items-center w-10 h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-100">
                                        <!-- pin icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 11.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19.5 9.5c0 5.25-7.5 11-7.5 11S4.5 14.75 4.5 9.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold">साङ्बाङ्गु</h3>
                                        <span class="text-xs text-slate-500">स्थान</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- body -->
                        <div class="p-4">
                            <p class="text-sm text-slate-700">
                                थुङ (पानी पिउने चराको खेल्ने स्थल) बाट नाम; थेचम्बु/फावाखोला किनार।
                            </p>
                        </div>
                    </article>

                    <article data-reveal
                        class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
                        <div class="relative">
                            <div class="h-2 bg-gradient-to-r from-sky-500 via-indigo-500 to-cyan-500"></div>
                            <div class="px-4 pt-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid place-items-center w-10 h-10 rounded-xl bg-sky-50 ring-1 ring-sky-100">
                                        <!-- mountain icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-sky-600"
                                            viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M3 20h18L13 4l-3 6-2-2-5 12z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold">चोक्सि डाँडा</h3>
                                        <span class="text-xs text-slate-500">उच्च थलो</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-slate-700">
                                फावाखोलामाथि; शिदीहाङको यक (गढी) का भग्नावशेष।
                            </p>
                        </div>
                    </article>

                    <article data-reveal
                        class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
                        <div class="relative">
                            <div class="h-2 bg-gradient-to-r from-fuchsia-500 via-rose-500 to-orange-500"></div>
                            <div class="px-4 pt-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="relative grid place-items-center w-10 h-10 rounded-xl bg-rose-50 ring-1 ring-rose-100">
                                        <!-- ripple dot -->
                                        <span
                                            class="absolute inline-flex h-8 w-8 rounded-full bg-rose-400/30 animate-ping"></span>
                                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold">येमासेन</h3>
                                        <span class="text-xs text-slate-500">पोखरी–थलो</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-slate-700">
                                विजुवानीको दन्त्यकथासँग जोडिएको पोखरी–थलो।
                            </p>
                        </div>
                    </article>

                    <article data-reveal
                        class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1">
                        <div class="relative">
                            <div class="h-2 bg-gradient-to-r from-amber-500 via-lime-500 to-emerald-500"></div>
                            <div class="px-4 pt-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid place-items-center w-10 h-10 rounded-xl bg-amber-50 ring-1 ring-amber-100">
                                        <!-- castle/fort icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600"
                                            viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M4 4h4v3H6v2h4V4h4v3h-2v2h4V4h4v8h-2v8H6v-8H4V4z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold">हस्तपुर यक (हाङ्पु यक)</h3>
                                        <span class="text-xs text-slate-500">याङरुप राजधानी</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-slate-700">
                                याङरुप थुम राजधानी; थिन्दोलुङ खोयाहाङको सत्ता–केन्द्र।
                            </p>
                        </div>
                    </article>

                    <article data-reveal
                        class="opacity-0 translate-y-4 transition-all duration-700 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 lg:col-span-2">
                        <div class="relative">
                            <div class="h-2 bg-gradient-to-r from-cyan-500 via-teal-500 to-emerald-500"></div>
                            <div class="px-4 pt-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid place-items-center w-10 h-10 rounded-xl bg-teal-50 ring-1 ring-teal-100">
                                        <!-- water/waves icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-teal-600"
                                            viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M3 15s2 2 5 2 5-2 5-2 2 2 5 2 3-2 3-2v3s-1 2-3 2-5-2-5-2-2 2-5 2-5-2-5-2v-3z" />
                                            <path
                                                d="M3 10s2 2 5 2 5-2 5-2 2 2 5 2 3-2 3-2V7s-1 2-3 2-5-2-5-2-2 2-5 2-5-2-5-2v3z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="font-semibold">हाङ्साम पोखरी / रानी पोखरी</h3>
                                        <span class="text-xs text-slate-500">किवदन्तीय जलस्रोत</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-slate-700">
                                थाङ्सामा/हाङ्सामबुनसँग सम्बन्धित किवदन्तीय जलस्रोतहरू (ओजङ्बुङ, चाँगे, थेचम्बु)।
                            </p>
                        </div>
                    </article>
                </div>
            </section>



            <!-- Wrapper -->



            <!-- DISPERSAL & NOTES -->
            <section class="mt-10">
                <h2 class="text-lg font-semibold">बसोबास/प्रसार र नोटहरू</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
                        <h3 class="font-semibold">प्रसार</h3>
                        <ul class="list-disc ms-5 mt-2 text-slate-700">
                            <li>मूल थलो: ताप्लेजुङ (थेचम्बु, साङ्बाङ्गु/सेक्रे); धनकुटा/संखुवासभा वरिपरि।</li>
                            <li>भारत (दार्जिलिङ, खर्साङ, पुल बजार, आसाम), सिक्किम, वर्मा (म्यानमार) सम्म बसोबास।</li>
                            <li>गोर्खा एकीकरण र सिक्किम–गोर्खा द्वन्द्वपछिका बसाइसरुवा तरंगहरू।</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
                        <h3 class="font-semibold">सन्दर्भ/मान्यता</h3>
                        <ul class="list-disc ms-5 mt-2 text-slate-700">
                            <li>स्व. इमानसिंह चेम्जोङका ग्रन्थहरूमा थिन्दोलुङ–खोयाहाङ/लिम्बुवान प्रसंगहरू।</li>
                            <li>‘मुनातेम्बे’ मूलथलोबारे भिन्न मत—पर्सिया/इरान आदि बारे थप शोध आवश्यक।</li>
                            <li>वंशावली, किवदन्ती र स्थानीय मौखिक परम्पराले पूरक विवरण दिन्छन्।</li>
                        </ul>
                    </div>
                </div>

                <!-- Notes collapsible -->
                <details class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 open:shadow-sm">
                    <summary class="cursor-pointer font-semibold select-none">अधिक नोट (संक्षेप खोल्न क्लिक गर्नुहोस्)
                    </summary>
                    <ul class="list-disc ms-5 mt-2 text-slate-700">
                        <li>मेयाङ्बो र आङ्बोहाङ—लाहाङ/नामहाङ दाजुभाइबाट छुट्टिएको परम्परा।</li>
                        <li>फावाखोलाको चोक्सि डाँडामा यक/गढी र धनु–विष प्रणालीको उल्लेख।</li>
                        <li>काभ्रे खोलामा पैतालाको छाप—थुमबीच सिमाङ्कनको स्थानीय स्मृति।</li>
                    </ul>
                </details>
            </section>

            <!-- GALLERY -->
            <section class="mt-10">
                <div class="flex items-end justify-between gap-2">
                    <h2 class="text-lg font-semibold">ऐतिहासिक फोटो ग्यालेरी </h2>
                </div>

                <div class="mt-4 grid gap-4g grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/काइलुङधुङ.png') }}" alt="ऐकाइलुङधुङ"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">काइलुङधुङ</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/केघिङदेन.png') }}" alt="केघिङदेन"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">केघिङदेन (याक्‍थुङबा जातिको बाजा बनाएको
                            स्‍थान)
                        </figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/खाप्‍पुरूङ खाप्‍मुरूङ.png') }}" alt="खाप्‍पुरूङ खाप्‍मुरूङ"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">खाप्‍पुरूङ खाप्‍मुरूङ</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/गुफा पोखरी.jpg') }}" alt="गुफा पोखरी"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">गुफा पोखरी</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/छाते लुङ.png') }}" alt="छाते लुङ"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">छाते लुङ</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/तिनचुरे  कोक्मा .jpg') }}" alt="तिनचुरे  कोक्मा"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">तिनचुरे कोक्मा</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/नेन्‍दुरी पासाङगा.png') }}" alt="नेन्‍दुरी पासाङगा"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">नेन्‍दुरी पासाङगा</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/पिपुधाप.jpg') }}" alt="पिपुधाप"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">पिपुधाप</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/फक्‍ताङलुङमा.png') }}" alt="फक्‍ताङलुङमा"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">फक्‍ताङलुङमा</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/फाक्‍थेक   (ताप्‍लेजुङ).png') }}" alt="फाक्‍थेक   (ताप्‍लेजुङ)"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">फाक्‍थेक (ताप्‍लेजुङ)</figcaption>
                    </figure>


                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/माङयाक तेम्‍बे .png') }}" alt="माङयाक तेम्‍बे"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">माङयाक तेम्‍बे</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/लालासो तुम्‍दुम्‍सो पाङभे.jpg') }}"
                                alt="लालासो तुम्‍दुम्‍सो पाङभे" class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">लालासो तुम्‍दुम्‍सो पाङभे</figcaption>
                    </figure>
                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/लेलेप् पाङभे.png') }}" alt="लेलेप् पाङभे"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">लेलेप् पाङभे</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/लोक्‍फादेन हाङफादेन.png') }}" alt="लोक्‍फादेन हाङफादेन"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">लोक्‍फादेन हाङफादेन</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/ससि फुक्‍को .jpg') }}" alt="ससि फुक्‍को"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">ससि फुक्‍को</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/ससि फुक्‍को .jpg') }}" alt="ससि फुक्‍को"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">ससि फुक्‍को</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/सुसुवेङ लालावेङ खेली हुर्केके स्थान.png') }}"
                                alt="सुसुवेङ लालावेङ खेली हुर्केके स्थान" class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">सुसुवेङ लालावेङ खेली हुर्केके स्थान
                        </figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/हाङसेनलुङ  (धनकुटा बोधे ).png') }}"
                                alt="हाङसेनलुङ  (धनकुटा बोधे )" class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">हाङसेनलुङ (धनकुटा बोधे )</figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/हाङसेनलुङ याक्‍थुङ राजा   छुटीएको स्‍थान.jpg') }}"
                                alt="हाङसेनलुङ याक्‍थुङ राजा छुटीएको स्‍थान" class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">हाङसेनलुङ याक्‍थुङ राजा छुटीएको स्‍थान
                        </figcaption>
                    </figure>

                    <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="aspect-video bg-slate-100">
                            <img src="{{ asset('places/हिलिहाङ दरबार.png') }}" alt="हिलिहाङ दरबार"
                                class="h-full w-full object-cover">
                        </div>
                        <figcaption class="text-xs text-slate-600 px-4 py-2">हिलिहाङ दरबार</figcaption>
                    </figure>
                </div>
            </section>

        </main>
    </div>

@endsection
