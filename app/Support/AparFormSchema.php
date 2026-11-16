<?php

namespace App\Support;

class AparFormSchema
{
    public static function steps(): array
    {
        return [
            [
                'title' => 'Personal & academic profile',
                'subtitle' => 'Part A · General information and academic background',
                'blocks' => [
                    self::fields('Personal information', [
                        self::field('personal.name', 'Name (in block letters)', 'text', ['required' => true]),
                        self::field('personal.parent_name', "Father's / Mother's name"),
                        self::field('personal.date_of_birth', 'Date of birth', 'date'),
                        self::field('personal.place_of_birth', 'Place of birth'),
                        self::field('personal.sex', 'Sex', 'select', ['options' => ['Female', 'Male', 'Other', 'Prefer not to say']]),
                        self::field('personal.marital_status', 'Marital status', 'select', ['options' => ['Single', 'Married', 'Other', 'Prefer not to say']]),
                        self::field('personal.nationality', 'Nationality'),
                        self::field('personal.department', 'Department', 'text', ['required' => true]),
                        self::field('personal.designation_level', 'Current designation and academic level', 'text', ['required' => true]),
                        self::field('personal.last_appointment_promotion', 'Date of last appointment / promotion', 'text'),
                        self::field('personal.category', 'Category (SC / ST / OBC / PWD / General)'),
                        self::field('personal.correspondence_address', 'Address for correspondence (with PIN code)', 'textarea'),
                        self::field('personal.permanent_address', 'Permanent address', 'textarea'),
                        self::field('personal.specialization', 'Field of specialization / discipline', 'textarea'),
                    ]),
                    self::table('academic_qualifications', 'Academic qualifications', [
                        self::field('examination', 'Examination'),
                        self::field('name', 'Name of exam'),
                        self::field('university', 'University'),
                        self::field('year', 'Year of passing', 'number'),
                        self::field('percentage', 'Percentage of marks', 'number', ['step' => '0.01']),
                        self::field('division', 'Division / class / grade'),
                        self::field('subjects', 'Main subjects'),
                    ]),
                    self::table('research_degrees', 'Research degrees', [
                        self::field('degree', 'Degree'),
                        self::field('university', 'University'),
                        self::field('thesis_title', 'Dissertation / thesis title'),
                        self::field('submitted_on', 'Date of submission', 'date'),
                        self::field('awarded_on', 'Date of award', 'date'),
                    ]),
                    self::table('undergraduate_courses', 'Courses taught · Undergraduate', [
                        self::field('year', 'Year', 'number'),
                        self::field('course', 'Course / paper'),
                        self::field('year_semester', 'Year / semester'),
                    ]),
                    self::table('postgraduate_courses', 'Courses taught · Postgraduate', [
                        self::field('year', 'Year', 'number'),
                        self::field('course', 'Course / paper'),
                        self::field('year_semester', 'Year / semester'),
                    ]),
                ],
            ],
            [
                'title' => 'Teaching & student activities',
                'subtitle' => 'Part B · Sections B.1 and B.2',
                'blocks' => [
                    self::table('professional_development', 'Courses, programmes and workshops attended · 17.1', [
                        self::field('details', 'Details / contribution / involvement'),
                        self::field('place', 'Place'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('year', 'Year', 'number'),
                        self::field('sponsor', 'Sponsoring / organizing agency'),
                    ]),
                    self::table('moocs_completed', 'MOOCs completed with e-certification · 17.2', [
                        self::field('details', 'MOOC / course details'),
                        self::field('platform', 'Platform / institution'),
                        self::field('year', 'Year', 'number'),
                        self::field('certificate_reference', 'Certificate / reference'),
                    ]),
                    self::table('econtent_mooc_development', 'E-content / MOOCs in four quadrants · 17.3', [
                        self::field('details', 'Course / content details'),
                        self::field('quadrant', 'Quadrant / contribution'),
                        self::field('year', 'Year', 'number'),
                        self::field('organization', 'Organization'),
                    ]),
                    self::table('mooc_conduct', 'Contribution towards conduct of MOOCs · 17.4', [
                        self::field('details', 'MOOC / contribution details'),
                        self::field('role', 'Role'),
                        self::field('year', 'Year', 'number'),
                        self::field('organization', 'Organization'),
                    ]),
                    self::table('teaching_activities', 'Teaching activity · B.1', [
                        self::field('year', 'Year', 'number'),
                        self::field('course_paper', 'Course / paper'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('mode', 'Teaching mode'),
                        self::field('activities', 'Lectures / tutorials / practicals / other activities'),
                        self::field('classes_assigned', 'Classes assigned', 'number'),
                        self::field('classes_taught', 'Classes taught', 'number'),
                    ]),
                    self::fields('Teaching grading guide', [
                        self::field('teaching.grading_guide', 'Teaching activity grading criteria', 'readonly', [
                            'value' => '80% and above: Good · 70% to below 80%: Satisfactory · Below 70%: Not Satisfactory. The percentage is calculated from classes taught / assigned.',
                        ]),
                    ]),
                    self::table('administrative_responsibilities', 'Administrative responsibilities · B.2(a)', [
                        self::field('nature', 'Nature of activity'),
                        self::field('designation', 'Designation'),
                        self::field('institution', 'Institution / department'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                    ]),
                    self::table('examination_duties', 'Examination and evaluation duties · B.2(b)', [
                        self::field('nature', 'Nature of activity'),
                        self::field('designation', 'Designation'),
                        self::field('institution', 'Institution / department'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                    ]),
                    self::table('student_activities', 'Student co-curricular, extension and field activities · B.2(c)', [
                        self::field('nature', 'Activity (clubs, counselling, visits, seminars, cultural, sports, NCC, NSS, community service, etc.)'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('institution', 'Institution / department'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                    ]),
                    self::table('organized_events', 'Seminars, conferences, workshops and other institutional activities · B.2(d)', [
                        self::field('year', 'Year', 'number'),
                        self::field('details', 'Details'),
                        self::field('place', 'Place'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('sponsor', 'Sponsoring / organizing agency'),
                    ]),
                    self::table('phd_guidance_b2', 'Ph.D. student guidance · B.2(e)', [
                        self::field('level', 'Level of guidance'),
                        self::field('registered', 'Registered'),
                        self::field('thesis_submitted', 'Thesis / dissertation submitted'),
                        self::field('degree_awarded', 'Degree awarded'),
                    ]),
                    self::table('sponsored_projects_b2', 'Sponsored minor / major research projects · B.2(f)', [
                        self::field('title', 'Title'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('type', 'Major / minor'),
                        self::field('sponsor', 'Sponsoring / funding agency'),
                    ]),
                    self::table('peer_reviewed_b2', 'Peer-reviewed / UGC-listed journal publications · B.2(g)', self::publicationFields()),
                    self::fields('B.2 self-assessment', [
                        self::field('b2.grading_guide', 'B.2 grading criteria', 'readonly', [
                            'value' => 'Good: involved in at least 3 activities · Satisfactory: involved in 1–2 activities · Not Satisfactory: no activities undertaken.',
                        ]),
                        self::field('b2.activities_covered', 'Number of activities covered', 'number'),
                        self::field('b2.self_grade', 'Self-assessed overall grading for B.2', 'select', [
                            'options' => ['Good', 'Satisfactory', 'Not Satisfactory'],
                        ]),
                    ]),
                ],
            ],
            [
                'title' => 'Research & publications',
                'subtitle' => 'Part B · Section B.3 · Publications and scholarly work',
                'blocks' => [
                    self::table('research_papers', 'Published research papers in referred / peer-reviewed / UGC-listed journals · B.3(1)', self::publicationFields()),
                    self::table('books_authored', 'Books authored · B.3(2)(a)(i)', [
                        self::field('title', 'Title of book'),
                        self::field('editorship', 'Authorship / editorship'),
                        self::field('publisher_level', 'Publisher level', 'select', ['options' => ['National', 'International']]),
                        self::field('publisher_details', 'Publisher (city / country / year)'),
                        self::field('isbn', 'ISBN'),
                        self::field('refereed', 'Whether refereed', 'select', ['options' => ['Yes', 'No']]),
                    ]),
                    self::table('edited_book_chapters', 'Chapters in edited books · B.3(2)(a)(ii)', [
                        self::field('book_title', 'Title of edited book'),
                        self::field('chapter_title', 'Title of chapter'),
                        self::field('authorship', 'Authorship'),
                        self::field('publisher_details', 'Publisher (city / country / year)'),
                        self::field('isbn', 'ISBN'),
                        self::field('refereed', 'Whether refereed', 'select', ['options' => ['Yes', 'No']]),
                    ]),
                    self::table('books_edited', 'Books edited · B.3(2)(a)(iii)', [
                        self::field('title', 'Title of edited book'),
                        self::field('editorship', 'Editorship'),
                        self::field('publisher_level', 'Publisher level', 'select', ['options' => ['National', 'International']]),
                        self::field('publisher_details', 'Publisher (city / country / year)'),
                        self::field('isbn', 'ISBN'),
                        self::field('refereed', 'Whether refereed', 'select', ['options' => ['Yes', 'No']]),
                    ]),
                    self::table('translated_chapters', 'Translation · chapters or research papers · B.3(2)(b)(i)', [
                        self::field('title', 'Title of paper / chapter'),
                        self::field('journal_book', 'Journal / book name'),
                        self::field('year', 'Year', 'number'),
                        self::field('volume', 'Volume number'),
                        self::field('page', 'Page number'),
                        self::field('issn_isbn', 'ISSN / ISBN'),
                        self::field('impact_factor', 'Impact factor', 'number', ['step' => '0.01']),
                        self::field('authorship', 'Authorship'),
                        self::field('ugc_reference', 'UGC-CARE reference'),
                    ]),
                    self::table('translated_books', 'Books translated · B.3(2)(b)(ii)', [
                        self::field('title', 'Title of book'),
                        self::field('authorship', 'Authorship'),
                        self::field('publisher_details', 'Publisher (city / country / year)'),
                        self::field('isbn', 'ISBN'),
                        self::field('refereed', 'Whether refereed', 'select', ['options' => ['Yes', 'No']]),
                    ]),
                ],
            ],
            [
                'title' => 'Innovation & digital learning',
                'subtitle' => 'Part B · Section B.3(3) · ICT pedagogy, curricula, MOOCs and e-content',
                'blocks' => [
                    self::table('innovative_pedagogy', 'Development of innovative pedagogy · B.3(3)(a)', [
                        self::field('module', 'Name of module'),
                        self::field('subject', 'Subject'),
                        self::field('stream', 'Stream'),
                        self::field('organization', 'Organization developed for'),
                        self::field('year', 'Year', 'number'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('new_curricula', 'Design of new curricula and courses · B.3(3)(b)', [
                        self::field('name', 'Name of curriculum / course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('year', 'Year', 'number'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('mooc_complete_courses', 'Complete MOOCs in four quadrants for a credit course · B.3(3)(c.1)', [
                        self::field('details', 'MOOC details and quadrants'),
                        self::field('credits', 'Credits', 'number'),
                        self::field('course', 'Course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('mooc_modules', 'MOOCs developed in four quadrants per module / lecture · B.3(3)(c.2)', [
                        self::field('details', 'MOOC details and quadrants'),
                        self::field('module', 'Module details'),
                        self::field('course', 'Course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('mooc_subject_expert', 'MOOC content writer / subject matter expert · B.3(3)(c.3)', [
                        self::field('details', 'MOOC details and quadrants'),
                        self::field('module', 'Module details'),
                        self::field('course', 'Course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('mooc_coordinator', 'MOOC course coordinator for a four-credit course · B.3(3)(c.4)', [
                        self::field('details', 'MOOC details and quadrants'),
                        self::field('credits', 'Credits', 'number'),
                        self::field('course', 'Course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('econtent_complete', 'E-content in four quadrants for a complete course / e-book · B.3(3)(d.1)', [
                        self::field('details', 'E-content details and quadrants'),
                        self::field('course_ebook', 'Course / e-book'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('econtent_modules', 'E-content in four quadrants per module · B.3(3)(d.2)', [
                        self::field('details', 'E-content details and quadrants'),
                        self::field('course_ebook', 'Course / e-book'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('econtent_contributions', 'Contribution to e-content for a course / paper / e-book · B.3(3)(d.3)', [
                        self::field('details', 'E-content details and quadrants'),
                        self::field('module', 'Module details'),
                        self::field('course', 'Course'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                    self::table('econtent_editor', 'Editor of e-content for a complete course / paper / e-book · B.3(3)(d.4)', [
                        self::field('details', 'E-content details and quadrants'),
                        self::field('course_ebook', 'Course / paper / e-book'),
                        self::field('subject', 'Subject'),
                        self::field('organization', 'Organization developed for'),
                        self::field('level', 'Level', 'select', ['options' => ['UG', 'PG', 'Other']]),
                        self::field('weblink', 'Web link', 'url'),
                    ]),
                ],
            ],
            [
                'title' => 'Research impact & recognition',
                'subtitle' => 'Part B · Section B.3(4–6) · Guidance, projects, patents, awards and lectures',
                'blocks' => [
                    self::table('research_guidance', 'Research guidance · B.3(4)(a)', [
                        self::field('level', 'Level of guidance'),
                        self::field('year', 'Year', 'number'),
                        self::field('registered', 'Registered'),
                        self::field('thesis_submitted', 'Thesis / dissertation submitted'),
                        self::field('degree_awarded', 'Degree awarded'),
                    ]),
                    self::table('completed_projects', 'Research projects completed · B.3(4)(b)', [
                        self::field('title', 'Title'),
                        self::field('pi_status', 'Status of PI (sole PI / PI / Co-PI)'),
                        self::field('duration_months', 'Duration (months)', 'number'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('funding', 'Total grant / funding received (Rs.)', 'number', ['step' => '0.01']),
                        self::field('sponsor', 'Sponsoring / funding agency'),
                        self::field('outcome', 'Outcome of project', 'textarea'),
                    ]),
                    self::table('ongoing_projects', 'Research projects ongoing · B.3(4)(c)', [
                        self::field('title', 'Title'),
                        self::field('pi_status', 'Status of PI (sole PI / PI / Co-PI)'),
                        self::field('duration_months', 'Duration (months)', 'number'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('funding', 'Total grant / funding received (Rs.)', 'number', ['step' => '0.01']),
                        self::field('sponsor', 'Sponsoring / funding agency'),
                        self::field('outcome', 'Outcome of project', 'textarea'),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                    self::table('consultancy', 'Consultancy · B.3(4)(d)', [
                        self::field('title', 'Title'),
                        self::field('duration_months', 'Duration (months)', 'number'),
                        self::field('period_from', 'Period from', 'date'),
                        self::field('period_to', 'Period to', 'date'),
                        self::field('funding', 'Total grant / funding received (Rs.)', 'number', ['step' => '0.01']),
                        self::field('sponsor', 'Sponsoring / funding agency'),
                        self::field('routed_through_university', 'Routed through parent university / college?', 'select', ['options' => ['Yes', 'No']]),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                    self::table('patents', 'Patents awarded · B.3(5)(a)', [
                        self::field('title', 'Title'),
                        self::field('area', 'Area / subject'),
                        self::field('awarded_on', 'Date of award', 'date'),
                        self::field('level', 'Level', 'select', ['options' => ['International', 'National']]),
                        self::field('reference', 'Reference number'),
                        self::field('sponsor', 'Sponsoring agency'),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                    self::table('policy_documents', 'Policy documents submitted to qualifying bodies · B.3(5)(b)', [
                        self::field('title', 'Title'),
                        self::field('pi_status', 'Status of PI / contribution'),
                        self::field('area', 'Area / subject'),
                        self::field('published_on', 'Date of publication / submission', 'date'),
                        self::field('level', 'Level', 'select', ['options' => ['International', 'National', 'State']]),
                        self::field('reference', 'Reference number'),
                        self::field('sponsor', 'Sponsoring agency'),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                    self::table('awards', 'Awards and fellowships · B.3(5)(c)', [
                        self::field('name', 'Name of award / honour'),
                        self::field('area', 'Area / subject'),
                        self::field('awarding_body', 'Awarding body'),
                        self::field('level', 'Level', 'select', ['options' => ['International', 'National']]),
                        self::field('date', 'Date', 'date'),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                    self::table('invited_lectures', 'Invited lectures, resource person, paper presentations and proceedings · B.3(6)', [
                        self::field('title', 'Title of lecture / paper'),
                        self::field('nature', 'Invited lecture / resource person / paper presented'),
                        self::field('programme', 'Nature of programme (seminar / FDP / conference)'),
                        self::field('details', 'Details'),
                        self::field('level', 'Level (international abroad / within country / national / state / university)'),
                        self::field('presented_on', 'Date of presentation', 'date'),
                        self::field('duration_minutes', 'Duration (minutes)', 'number'),
                        self::field('sponsor', 'Sponsoring agency'),
                        self::field('research_score', 'Academic research score', 'number', ['step' => '0.01']),
                    ]),
                ],
            ],
            [
                'title' => 'Other information & declaration',
                'subtitle' => 'Part C and Part D · Complete, check, and submit',
                'blocks' => [
                    self::fields('Other relevant information · Part C', [
                        self::field('other.memberships', 'Membership / fellowship of learned bodies or societies', 'textarea'),
                        self::field('other.literary_activities', 'Literary, cultural, sports or other activities and distinctions', 'textarea'),
                        self::field('other.credentials', 'Other credentials, significant contributions, awards or responsibilities', 'textarea'),
                        self::field('other.future_plans', 'Future plans (approximately 150 words)', 'textarea', ['maxlength' => 1500]),
                        self::field('other.enclosures', 'List of enclosures / supporting evidence included', 'textarea'),
                    ]),
                    self::fields('Declaration · Part D', [
                        self::field('declaration.confirmed', 'I confirm that the information and supporting records provided are complete and accurate, and that I have read and agree to the applicable guidelines.', 'checkbox', ['required' => true]),
                        self::field('declaration.date', 'Date', 'date', ['required' => true]),
                        self::field('declaration.place', 'Place', 'text', ['required' => true]),
                    ]),
                ],
            ],
        ];
    }

    public static function emptyData(): array
    {
        $data = [
            'declaration' => ['confirmed' => false],
            'enclosures' => [],
        ];

        foreach (self::steps() as $step) {
            foreach ($step['blocks'] as $block) {
                if ($block['type'] === 'fields') {
                    foreach ($block['fields'] as $field) {
                        data_set($data, $field['key'], $field['type'] === 'checkbox' ? false : '');
                    }
                } else {
                    data_set($data, $block['key'], []);
                }
            }
        }

        return $data;
    }

    public static function tableFields(string $key): ?array
    {
        foreach (self::steps() as $step) {
            foreach ($step['blocks'] as $block) {
                if ($block['type'] === 'table' && $block['key'] === $key) {
                    return $block['fields'];
                }
            }
        }

        return null;
    }

    private static function fields(string $title, array $fields): array
    {
        return ['type' => 'fields', 'title' => $title, 'fields' => $fields];
    }

    private static function table(string $key, string $title, array $fields): array
    {
        return ['type' => 'table', 'key' => $key, 'title' => $title, 'fields' => $fields];
    }

    private static function publicationFields(): array
    {
        return [
            self::field('title', 'Title of paper'),
            self::field('journal', 'Journal name'),
            self::field('year', 'Year', 'number'),
            self::field('volume', 'Volume'),
            self::field('pages', 'Page number'),
            self::field('issn', 'ISSN number'),
            self::field('impact_factor', 'Impact factor', 'number', ['step' => '0.01']),
            self::field('scopus_indexed', 'Scopus indexed?', 'select', ['options' => ['Yes', 'No']]),
            self::field('authorship', 'Authorship'),
            self::field('ugc_reference', 'UGC-CARE reference number'),
        ];
    }

    private static function field(string $key, string $label, string $type = 'text', array $options = []): array
    {
        return array_merge(['key' => $key, 'label' => $label, 'type' => $type], $options);
    }
}
