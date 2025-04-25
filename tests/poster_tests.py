import unittest
import mypy
import re
from selenium import webdriver

# from selenium.webdriver.common.keys import Keys
# from selenium.webdriver.common.by import By
# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary


class PythonOrgSearch(unittest.TestCase):

    def setUp(self):
        options = Options()
        options.add_argument("--headless")

        # print("Firefox options binary:", options.binary_location)
        # options.binary_location = "/usr/bin/firefox"
        # print("Firefox options binary:", options.binary_location)
        # print("Firefox options binary:", options.binary)

        self.driver = webdriver.Firefox(options=options)

    def test_search_in_python_org(self):
        driver = self.driver
        # driver.get("http://localhost/scientific_poster_generator/login.php")
        # self.assertIn("Poster Generator", driver.title)

        driver.get("https://example.com")
        print(driver.title)

        driver.quit()

        # self.login(self.driver, "max5", "abc")
        # self.projects(self.driver)
        # self.poster(self.driver)

        # self.author(self.driver)

        # self.delete_last_poster(self.driver)

        # # self.logout(self.driver)

        # self.admin(self.driver)

    # def tearDown(self):
    #     self.driver.close()

    # def login(self, driver, name: str, pw: str):

    #     filed_name = driver.find_element(By.ID, "name")
    #     filed_name.send_keys(name + "dhsblh")
    #     filed_name.send_keys(Keys.RETURN)

    #     filed_pw = driver.find_element(By.ID, "pw")
    #     filed_pw.send_keys(pw)
    #     filed_pw.send_keys(Keys.RETURN)

    #     driver.find_element(By.ID, "login").click()
    #     self.assertEqual(
    #         "http://localhost/scientific_poster_generator/login.php",
    #         driver.current_url,
    #     )

    #     driver.find_element(By.ID, "name").clear()
    #     driver.find_element(By.ID, "pw").clear()

    #     filed_name = driver.find_element(By.ID, "name")
    #     filed_name.send_keys(name)
    #     filed_name.send_keys(Keys.RETURN)

    #     filed_pw = driver.find_element(By.ID, "pw")
    #     filed_pw.send_keys(pw)
    #     filed_pw.send_keys(Keys.RETURN)

    #     driver.find_element(By.ID, "login").click()

    #     self.assertEqual(
    #         "http://localhost/scientific_poster_generator/projects.php",
    #         driver.current_url,
    #     )

    #     session_id = driver.get_cookie("sessionID")
    #     self.assertIsNotNone(session_id)

    # def projects(self, driver):
    #     self.assertEqual(
    #         "http://localhost/scientific_poster_generator/projects.php",
    #         driver.current_url,
    #     )

    #     content_table = driver.find_element(
    #         By.CSS_SELECTOR, "#table-container>table"
    #     ).get_attribute("innerHTML")

    #     self.assertTrue("<th>title</th>" in content_table)
    #     self.assertTrue("<th>last_edit</th>" in content_table)

    #     content_line = (
    #         driver.find_element(By.CSS_SELECTOR, "#table-container>table")
    #         .get_attribute("innerHTML")
    #         .split("</tr>")[1]
    #         + "</tr>"
    #     )
    #     # self.assertTrue(
    #     #     re.search(
    #     #         '<tr id="nr-[0-9]*"><td>.*</td><td>20[2-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-2][0-9]:[0-6][0-9]:[0-6][0-9]</td><td><td><a>Edit</a></td></td><td><td><input type="button" class="btn" value="Delete"></td></td></tr>',
    #     #         content_line,
    #     #     )
    #     # )

    #     self.assertTrue(re.search("<td><td><a>.*</a></td></td>", content_line))
    #     self.assertTrue(
    #         re.search('<td><td><input .* value="Delete"></td></td>', content_line)
    #     )

    #     filed_title = driver.find_element(By.ID, "project-name")
    #     filed_title.send_keys("test title")
    #     filed_title.send_keys(Keys.RETURN)
    #     driver.find_element(By.CSS_SELECTOR, "#create-project>button").click()

    #     content_table_new = driver.find_element(
    #         By.CSS_SELECTOR, "#table-container>table"
    #     ).get_attribute("innerHTML")
    #     # last_nr = int(
    #     #     re.findall(
    #     #         "[0-9]+", re.findall('<tr id="nr-[0-9]+">', content_table_new)[-1]
    #     #     )[0]
    #     # )
    #     # driver.find_element(By.CSS_SELECTOR, f"#nr-{last_nr}>td>td>a").click()
    #     # self.assertEqual(
    #     #     "http://localhost/scientific_poster_generator/poster.php",
    #     #     driver.current_url.split("?")[0],
    #     # )

    # def delete_last_poster(self, driver):
    #     driver.get("http://localhost/scientific_poster_generator/projects.php")
    #     # driver.find_element(By.CSS_SELECTOR, "#load-form>button").click()

    #     content_table_new = driver.find_element(
    #         By.CSS_SELECTOR, "#table-container>table"
    #     ).get_attribute("innerHTML")
    #     # last_nr = int(
    #     #     re.findall(
    #     #         "[0-9]+", re.findall('<tr id="nr-[0-9]+">', content_table_new)[-1]
    #     #     )[0]
    #     # )
    #     # driver.find_element(By.CSS_SELECTOR, f"#nr-{last_nr}>td>td>input").click()
    #     content_table3 = driver.find_element(
    #         By.CSS_SELECTOR, "#table-container>table"
    #     ).get_attribute("innerHTML")
    #     # self.assertIsNone(re.search(f'<tr id="nr-{last_nr}">', content_table3))

    # def poster(self, driver):
    #     # self.assertEqual(
    #     #     "http://localhost/scientific_poster_generator/poster.php",
    #     #     driver.current_url.split("?")[0],
    #     # )
    #     # check right title
    #     # content = driver.find_element(By.ID, "boxes").get_attribute("innerHTML")
    #     # self.assertEqual("", content)
    #     # driver.find_element(By.ID, "add-box").click()

    #     # check title edit

    #     # print(driver.current_url)

    #     # check add Box
    #     # content = driver.find_element(By.ID, "boxes").get_attribute("innerHTML")
    #     # print(f"[{content}]")
    #     # self.assertEqual(
    #     #     '<div id="editBox-0" data-content="Content">Content</div>', content
    #     # )
    #     # reload page
    #     url = driver.current_url
    #     driver.get(url)
    #     # check if content not saved
    #     # content = driver.find_element(By.ID, "boxes").get_attribute("innerHTML")
    #     # self.assertEqual("", content)
    #     # add box + write simple text
    #     # driver.find_element(By.ID, "add-box").click()
    #     # driver.find_element(By.ID, "editBox-0").click()
    #     # text_field = driver.find_element(By.ID, "editBox-0")
    #     # text_field.send_keys("Test Text")
    #     # text_field.send_keys(Keys.RETURN)
    #     # driver.find_element(By.ID, "typeahead").click()
    #     # # click save
    #     # driver.find_element(By.ID, "save-content").click()
    #     # reload
    #     driver.refresh()
    #     # check if saved
    #     driver.implicitly_wait(1)
    #     # content = driver.find_element(By.CSS_SELECTOR, "#boxes>*").get_attribute(
    #     #     "outerHTML"
    #     # )
    #     # self.assertEqual(
    #     #     '<div id="editBox-0" data-content="ContentTest Text\n"><p>ContentTest Text</p>\n</div>',
    #     #     content,
    #     # )

    #     # check visibility test

    #     # check box delete

    #     # md render test
    #     # LaTeX render test

    #     # image upload test
    #     # imgae download test

    # # TODO: why does logout not work?
    # def logout(self, driver):
    #     driver.get("http://localhost/scientific_poster_generator/projects.php")

    #     driver.find_element(By.ID, "logout").click()

    #     self.assertEqual(
    #         "http://localhost/scientific_poster_generator/login.php", driver.current_url
    #     )

    #     session_id = driver.get_cookie("sessionID")
    #     self.assertIsNone(session_id)

    # def admin(self, driver):
    #     # test admin mode
    #     driver.get("http://localhost/scientific_poster_generator/login.php")

    #     self.login(driver, "Admin", "PwScaDS-2025")
    #     # check if successful

    #     # check if visibility attribute gets loaded
    #     # driver.find_element(By.CSS_SELECTOR, "#load-form>button").click()
    #     container = driver.find_element(
    #         By.CSS_SELECTOR, "#table-container>table>*"
    #     ).get_attribute("outerHTML")
    #     self.assertNotEqual("", container)

    #     # toggle visibility checkbox
    #     index = 1
    #     # driver.find_element(
    #     #     By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     # ).click()
    #     # toggleA = driver.find_element(
    #     #     By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     # ).get_attribute("value")

    #     # reload page and check if toggle is saved
    #     driver.refresh()
    #     # driver.find_element(By.CSS_SELECTOR, "#load-form>button").click()
    #     # toggleB = driver.find_element(
    #     #     By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     # ).get_attribute("value")
    #     # self.assertEqual(toggleA, toggleB)

    #     # check if activated poster is visible on index
    #     # toggle = driver.find_element(
    #     #     By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     # ).get_attribute("checked")

    #     # if toggle != "true":
    #     #     driver.find_element(
    #     #         By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     #     ).click()

    #     # toggle = driver.find_element(
    #     #     By.CSS_SELECTOR, f"#nr-{index} input[type='checkbox']"
    #     # ).get_attribute("checked")

    #     driver.get("http://localhost/scientific_poster_generator/index.php")

    #     # check if iframe exist
    #     # iframes = driver.find_element(By.CSS_SELECTOR, "#posters > *").get_attribute(
    #     #     "innerHTML"
    #     # )
    #     # self.assertNotEqual("", iframes)

    #     # access iframe
    #     iframes = driver.find_element(By.CSS_SELECTOR, "#posters").get_attribute(
    #         "children"
    #     )
    #     # for i in re.findall("<div.*><iframe.*>.*</iframe></div>", iframes):
    #     #     print(i.getAttributes("innerHTML"))
    #     # print(re.findall("<div.*><iframe.*>.*</iframe></div>", iframes))

    #     # Get all child elements inside #posters

    #     children = driver.find_elements(By.CSS_SELECTOR, "#posters > *")
    #     for child in children:
    #         print(child.get_attribute("outerHTML"))

    #     # check if iframe is correctly loaded
    #     # driver.switch_to.frame(
    #     #     driver.find_element(By.CSS_SELECTOR, "#posters > div > iframe")
    #     # )

    #     # title = (
    #     #     driver.find_element(By.CSS_SELECTOR, "#title > p")
    #     #     .get_attribute("innerHTML")
    #     #     .split("<mjx-container")[0]
    #     # )
    #     # # re.search(
    #     # # "\b[[:word:]]\b (?=.?<mjx-container)",
    #     # # )
    #     # self.assertEqual("Test Title ", title)

    #     driver.implicitly_wait(6)

    #     # switch back
    #     # driver.switch_to.default_content()

    # def author(self, driver):
    #     pass
    #     # add new author + save
    #     # refresh page
    #     # check if correctly loaded

    #     # delete author


if __name__ == "__main__":
    print("Hello World")

    unittest.main()
